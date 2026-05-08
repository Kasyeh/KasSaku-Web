<?php

namespace App\Services;

use App\Models\BalanceModel;
use App\Models\TransactionModel;
use App\Models\User;
use Carbon\Carbon;

class BalanceService
{
    public function getSnapshot(int $userId): ?array
    {
        $balance = BalanceModel::where('id_user', $userId)->first();

        if (!$balance) {
            return null;
        }

        $user = User::find($userId);

        $pemasukanSemua = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pemasukan')
            ->sum('nominal');
        $pengeluaranSemua = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pengeluaran')
            ->sum('nominal');
        $realSaldo = $pemasukanSemua - $pengeluaranSemua;

        $pemasukanBulanIni = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pemasukan')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('nominal');

        $pengeluaranBulanIni = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('nominal');

        if ((float) $balance->saldo !== (float) $realSaldo) {
            $balance->saldo = $realSaldo;
            $balance->save();

            TransactionService::refreshBalance($userId);
        }

        $targetPengeluaran = $balance->target_pengeluaran;

        return [
            'balance' => $balance,
            'user' => $user,
            'real_saldo' => $realSaldo,
            'pemasukan_bulan_ini' => $pemasukanBulanIni,
            'pengeluaran_bulan_ini' => $pengeluaranBulanIni,
            'target_pengeluaran' => $targetPengeluaran,
            'is_over_budget' => $targetPengeluaran && $pengeluaranBulanIni > $targetPengeluaran,
        ];
    }

    public function saveTargetPengeluaran(int $userId, float $targetPengeluaran): array
    {
        $balance = BalanceModel::firstOrCreate(
            ['id_user' => $userId],
            ['saldo' => 0, 'pemasukan' => 0, 'pengeluaran' => 0]
        );

        $balance->target_pengeluaran = $targetPengeluaran > 0 ? $targetPengeluaran : null;
        $balance->save();

        TransactionService::refreshBalance($userId);

        $pengeluaranBulanIni = TransactionModel::where('id_user', $userId)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', Carbon::now()->month)
            ->whereYear('tanggal', Carbon::now()->year)
            ->sum('nominal');

        return [
            'balance' => $balance,
            'pengeluaran_bulan_ini' => $pengeluaranBulanIni,
            'is_over_budget' => $balance->target_pengeluaran && $pengeluaranBulanIni > $balance->target_pengeluaran,
        ];
    }
}
