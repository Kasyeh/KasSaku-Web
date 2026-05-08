<?php

namespace App\Services;

use App\Models\BalanceModel;
use App\Models\TransactionModel;
use App\Models\User;
use App\Services\FirebaseService;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class TransactionService
{
    public static function createTransaction($id_user, $type, $nominal, $kategori, $keterangan, $tanggal = null, $icon = null)
    {
        // Clean nominal (remove dots)
        $nominal = str_replace('.', '', $nominal);

        // Ensure $tanggal has time if it's just a date (YYYY-MM-DD)
        if ($tanggal && strlen($tanggal) <= 10) {
            $tanggal = $tanggal . ' ' . now()->format('H:i:s');
        }

        $transaction = DB::transaction(function () use ($id_user, $type, $nominal, $kategori, $keterangan, $tanggal, $icon) {
            // Create transaction and update balance atomically
            $transaction = TransactionModel::create([
                'id_user' => $id_user,
                'tipe' => $type,
                'nominal' => $nominal,
                'kategori' => $kategori,
                'keterangan' => $keterangan,
                'icon' => $icon,
                'tanggal' => $tanggal ?? now(),
            ]);

            self::updateBalance($id_user, $type, $nominal, false);

            return $transaction;
        });

        // Sync external realtime store after DB commit to avoid partial state
        self::refreshBalance($id_user);

        // --- FCM NOTIFICATION ---
        try {
            $user = User::find($id_user);
            if ($user && $user->fcm_token) {
                $firebaseService = app(FirebaseService::class);

                // 1. Notifikasi Transaksi Berhasil
                $firebaseService->sendTransactionNotification($user, $type, $nominal);

                // 2. Budget Alert (Jika pengeluaran)
                if ($type === 'pengeluaran') {
                    $balance = BalanceModel::where('id_user', $id_user)->first();
                    if ($balance && $balance->target_pengeluaran) {
                        // Hitung pengeluaran bulan ini
                        $pengeluaranBulanIni = TransactionModel::where('id_user', $id_user)
                            ->where('tipe', 'pengeluaran')
                            ->whereMonth('tanggal', Carbon::now()->month)
                            ->whereYear('tanggal', Carbon::now()->year)
                            ->sum('nominal');

                        // Jika baru saja melewati budget
                        if (
                            $pengeluaranBulanIni > $balance->target_pengeluaran &&
                            ($pengeluaranBulanIni - $nominal) <= $balance->target_pengeluaran
                        ) {

                            $firebaseService->sendAdminNotification(
                                $user,
                                "⚠️ Budget Alert! Pengeluaran bulan ini (Rp " . number_format($pengeluaranBulanIni, 0, ',', '.') . ") sudah melebihi target Anda (Rp " . number_format($balance->target_pengeluaran, 0, ',', '.') . ").",
                                'budget_exceeded'
                            );
                        }
                    }
                }

                // 3. Dream Target Reach Check (Jika saldo bertambah)
                if ($type === 'pemasukan' || $type === 'reset') { // Reset might also make saldo reached if it was negative
                    $balance = BalanceModel::where('id_user', $id_user)->first();
                    if ($balance && $balance->saldo > 0) {
                        $dreams = \App\Models\ImpianModel::where('id_user', $id_user)->get();
                        foreach ($dreams as $dream) {
                            // Jika saldo baru >= harga barang DAN saldo lama < harga barang
                            $oldSaldo = ($type === 'pemasukan') ? ($balance->saldo - $nominal) : 0;

                            if ($balance->saldo >= $dream->harga_barang && $oldSaldo < $dream->harga_barang) {
                                $firebaseService->sendAdminNotification(
                                    $user,
                                    "🎉 Selamat! Saldo Anda (Rp " . number_format($balance->saldo, 0, ',', '.') . ") sudah cukup untuk membeli impian Anda: {$dream->nama_barang}! 🎯",
                                    'dream_reached'
                                );
                            }
                        }
                    }
                }
            }
        } catch (\Exception $e) {
            \Log::error('FCM Auto-Notification Error: ' . $e->getMessage());
        }

        return $transaction;
    }

    public static function updateBalance($id_user, $type, $nominal, $syncRealtime = true)
    {
        $balance = BalanceModel::where('id_user', $id_user)->lockForUpdate()->first();

        if ($balance) {
            if ($type === 'pemasukan') {
                $balance->pemasukan += $nominal;
                $balance->saldo += $nominal;
            } else {
                $balance->pengeluaran += $nominal;
                $balance->saldo -= $nominal;
            }
            $balance->save();
        } else {
            // Create new balance if doesn't exist
            $balance = BalanceModel::create([
                'id_user' => $id_user,
                'saldo' => $type === 'pemasukan' ? $nominal : -$nominal,
                'pemasukan' => $type === 'pemasukan' ? $nominal : 0,
                'pengeluaran' => $type === 'pengeluaran' ? $nominal : 0,
            ]);
        }

        if ($syncRealtime) {
            // Sync to Realtime Database
            try {
                $firebaseService = app(FirebaseService::class);
                $firebaseService->updateUserBalance($id_user, [
                    'saldo' => $balance->saldo,
                    'pemasukan' => $balance->pemasukan,
                    'pengeluaran' => $balance->pengeluaran,
                    'target_pengeluaran' => $balance->target_pengeluaran
                ]);
            } catch (\Exception $e) {
                \Log::error('RTDB Sync Error: ' . $e->getMessage());
            }
        }
    }

    public static function refreshBalance($id_user)
    {
        $balance = BalanceModel::where('id_user', $id_user)->first();
        if ($balance) {
            try {
                $firebaseService = app(FirebaseService::class);
                $firebaseService->updateUserBalance($id_user, [
                    'saldo' => $balance->saldo,
                    'pemasukan' => $balance->pemasukan,
                    'pengeluaran' => $balance->pengeluaran,
                    'target_pengeluaran' => $balance->target_pengeluaran
                ]);
            } catch (\Exception $e) {
                \Log::error('RTDB Refresh Balance Error: ' . $e->getMessage());
            }
        }
    }
}
