<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BalanceModel;
use App\Models\User;
use App\Models\MotivasiModel;
use App\Models\ImpianModel;
use App\Models\DreamItem;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use App\Models\TransactionModel;
use App\Models\PermintaanUnblockModel;
use App\Models\FeedbackModel;
use App\Services\FirebaseService;
use Illuminate\Support\Str;
use Session;

class AdminController extends Controller
{
    function dashboard()
    {
        return view('admin.admin');
    }

    public function pendingUnblockFeed()
    {
        $latestPending = PermintaanUnblockModel::with('user')
            ->where('status', 'pending')
            ->orderByDesc('created_at')
            ->first();

        return response()->json([
            'success' => true,
            'data' => [
                'pending_count' => PermintaanUnblockModel::where('status', 'pending')->count(),
                'latest_pending_timestamp' => (int) optional($latestPending?->created_at)->timestamp,
                'latest_pending' => $latestPending ? [
                    'id' => $latestPending->id,
                    'username' => $latestPending->user->username ?? 'Unknown User',
                    'message' => $latestPending->pesan,
                ] : null,
            ],
        ]);
    }

    public function dashAdmin()
    {
        // Hitung pemasukan, pengeluaran, saldo
        $pemasukan = TransactionModel::where('tipe', 'pemasukan')->sum('nominal');
        $pengeluaran = TransactionModel::where('tipe', 'pengeluaran')->sum('nominal');
        $saldo = $pemasukan - $pengeluaran;

        // Data grafik pemasukan & pengeluaran bulanan
        $grafik = TransactionModel::selectRaw("
        MONTH(tanggal) as bulan,
        SUM(CASE WHEN tipe = 'pemasukan' THEN nominal ELSE 0 END) as total_pemasukan,
        SUM(CASE WHEN tipe = 'pengeluaran' THEN nominal ELSE 0 END) as total_pengeluaran
    ")
            ->groupBy('bulan')
            ->orderBy('bulan')
            ->get();

        $labels = $grafik->map(fn($g) => "Bulan " . $g->bulan);
        $data_pemasukan = $grafik->pluck('total_pemasukan');
        $data_pengeluaran = $grafik->pluck('total_pengeluaran');

        // Transaksi terbaru
        $transaksi_terbaru = TransactionModel::orderBy('tanggal', 'desc')->take(5)->get();

        // Tambahan: jumlah user
        $totalUser = User::count();

        // Tambahan: motivasi (jika ada tabel motivasi)
        // Misal ambil random 1 motivasi dari tabel

        // Kalau belum ada tabel motivasi, bisa pakai array motivasi sederhan
        return view('admin.admin', [
            'total_pemasukan' => $pemasukan,
            'total_pengeluaran' => $pengeluaran,
            'saldo' => $saldo,
            'labels' => $labels,
            'pemasukan' => $data_pemasukan,
            'pengeluaran' => $data_pengeluaran,
            'transaksi_terbaru' => $transaksi_terbaru,
            'totalUser' => $totalUser,
        ]);
    }

    public function list_user()
    {
        $list_user = User::select('users.id_user', 'users.username', 'users.active', 'tb_saldo_user.saldo', 'tb_saldo_user.pemasukan', 'tb_saldo_user.pengeluaran')
            ->leftJoin('tb_saldo_user', 'tb_saldo_user.id_user', '=', 'users.id_user')
            ->where('users.role', '!=', 'admin')
            ->orderBy('users.id_user', 'asc')
            ->paginate(10);
        return view('admin.list_user', compact('list_user'));
    }
    public function cekUser($id)
    {
        // Ambil data user
        $user = User::find($id);

        if (!$user) {
            return redirect()->back()->with('error', 'User tidak ditemukan');
        }

        // Ambil saldo berdasarkan id_user
        $saldo = BalanceModel::where('id_user', $id)->first();

        // Ambil semua impian berdasarkan id_user
        $dreamItems = ImpianModel::where('id_user', $id)->get();

        // Ambil riwayat transaksi berdasarkan id_user
        $transactions = TransactionModel::where('id_user', $id)
            ->orderBy('tanggal', 'desc')
            ->get();

        // Ringkasan cepat
        $totalTransactions = TransactionModel::where('id_user', $id)->count();
        $lastTransaction = TransactionModel::where('id_user', $id)
            ->orderBy('tanggal', 'desc')
            ->first();
        $pendingUnblockCount = PermintaanUnblockModel::where('id_user', $id)
            ->where('status', 'pending')
            ->count();
        $hasPendingUnblockRequest = $pendingUnblockCount > 0;
        $latestUnblockRequest = PermintaanUnblockModel::where('id_user', $id)
            ->orderBy('updated_at', 'desc')
            ->first();
        $hasFcmToken = !empty($user->fcm_token);

        // Hitung pemasukan & pengeluaran untuk bulan ini
        $monthlyPemasukan = TransactionModel::where('id_user', $id)
            ->where('tipe', 'pemasukan')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('nominal');

        $monthlyPengeluaran = TransactionModel::where('id_user', $id)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', now()->month)
            ->whereYear('tanggal', now()->year)
            ->sum('nominal');

        return view('admin.detail_user', compact(
            'user',
            'saldo',
            'dreamItems',
            'transactions',
            'monthlyPemasukan',
            'monthlyPengeluaran',
            'totalTransactions',
            'lastTransaction',
            'hasPendingUnblockRequest',
            'pendingUnblockCount',
            'latestUnblockRequest',
            'hasFcmToken'
        ));
    }

    public function blockUser($id)
    {
        $user = User::findOrFail($id);
        $tokenCountBeforeRevoke = $user->tokens()->count();
        $user->active = 0;
        $user->remember_token = Str::random(60);
        $user->save();
        $user->tokens()->delete();

        \Log::info('Admin blocked user account', [
            'user_id' => (int) $user->id_user,
            'token_count_before_revoke' => $tokenCountBeforeRevoke,
            'token_count_after_revoke' => $user->tokens()->count(),
            'has_fcm_token' => !empty($user->fcm_token),
        ]);

        // Kirim notifikasi ke user
        $firebaseService = app(FirebaseService::class);
        $notificationSent = false;
        if ($user->fcm_token) {
            $notificationSent = $firebaseService->sendAdminNotification(
                $user,
                'Akun Anda telah diblokir oleh admin. Hubungi admin untuk informasi lebih lanjut.',
                'account_blocked'
            );
        }

        // Sync status ke Firebase
        $firebaseService->updateUserStatus($id, 0);
        $firebaseService->notifyUserAccountEvent(
            $id,
            'blocked',
            'Akun Anda telah diblokir oleh admin. Sesi akan diakhiri.'
        );

        \Log::info('Admin block propagation finished', [
            'user_id' => (int) $user->id_user,
            'fcm_notification_sent' => $notificationSent,
            'rtdb_status_target' => 0,
            'rtdb_account_event' => 'blocked',
        ]);

        return redirect()->back()->with('success', 'User berhasil diblokir');
    }

    public function unblockUser($id)
    {
        $user = User::findOrFail($id);
        $user->active = 1;
        $user->save();

        \Log::info('Admin unblocked user account', [
            'user_id' => (int) $user->id_user,
            'has_fcm_token' => !empty($user->fcm_token),
        ]);

        // Jika unblock dilakukan dari detail user, tandai semua request pending user ini sebagai dikabulkan.
        $updatedRequests = PermintaanUnblockModel::where('id_user', $id)
            ->where('status', 'pending')
            ->update([
                'status' => 'dikabulkan',
                'alasan_admin' => 'Disetujui via halaman detail user.'
            ]);

        // Sync status ke Firebase
        $firebaseService = app(FirebaseService::class);
        $firebaseService->updateUserStatus($id, 1);
        $firebaseService->notifyUserAccountEvent(
            $id,
            'unblocked',
            'Akun Anda sudah diaktifkan kembali.'
        );

        // Jika ada request pending yang disetujui, kirim response realtime ke Android.
        if ($updatedRequests > 0) {
            $firebaseService->notifyUnblockResponse(
                $id,
                'approved',
                'Permintaan unblock Anda telah disetujui! Akun sudah aktif kembali 🎉'
            );
        }

        // Kirim notifikasi ke user
        $notificationSent = false;
        if ($user->fcm_token) {
            $notificationSent = $firebaseService->sendAdminNotification(
                $user,
                'Akun Anda sudah diaktifkan kembali! Selamat menggunakan KasSaku 🎉',
                'account_unblocked'
            );
        }

        \Log::info('Admin unblock propagation finished', [
            'user_id' => (int) $user->id_user,
            'updated_requests' => (int) $updatedRequests,
            'fcm_notification_sent' => $notificationSent,
            'rtdb_status_target' => 1,
            'rtdb_account_event' => 'unblocked',
        ]);

        return redirect()->back()->with('success', 'User berhasil diunblock');
    }

    public function hapusUser($id)
    {
        $user = User::findOrFail($id);

        // Hapus data terkait
        DB::transaction(function () use ($user) {
            BalanceModel::where('id_user', $user->id_user)->delete();
            TransactionModel::where('id_user', $user->id_user)->delete();
            ImpianModel::where('id_user', $user->id_user)->delete();
            PermintaanUnblockModel::where('id_user', $user->id_user)->delete();
            $user->delete();
        });

        return redirect()->back()->with('success', 'User dan semua datanya berhasil dihapus');
    }

    public function permintaanUnblock()
    {
        $requests = PermintaanUnblockModel::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.permintaan_unblock', compact('requests'));
    }

    public function prosesUnblock(Request $request, $id)
    {
        $permintaan = PermintaanUnblockModel::findOrFail($id);
        $user = User::findOrFail($permintaan->id_user);

        if ($request->action == 'terima') {
            $user->active = 1;
            $user->save();
            $permintaan->status = 'dikabulkan';

            // Sync status ke Firebase
            $firebaseService = app(FirebaseService::class);
            $firebaseService->updateUserStatus($user->id_user, 1);
            $firebaseService->notifyUserAccountEvent(
                $user->id_user,
                'unblocked',
                'Permintaan unblock Anda telah disetujui. Akun aktif kembali.'
            );

            // Kirim notifikasi approval via RTDB (realtime untuk Android)
            $firebaseService->notifyUnblockResponse(
                $user->id_user,
                'approved',
                'Permintaan unblock Anda telah disetujui! Akun sudah aktif kembali 🎉'
            );

            // Kirim juga via FCM sebagai fallback
            if ($user->fcm_token) {
                $firebaseService->sendAdminNotification(
                    $user,
                    'Permintaan unblock Anda telah disetujui! Akun sudah aktif kembali 🎉',
                    'unblock_approved'
                );
            }
        } else {
            $permintaan->status = 'ditolak';
            $permintaan->alasan_admin = $request->alasan_admin;

            $msg = 'Maaf, permintaan unblock Anda ditolak oleh admin.';
            if ($request->alasan_admin) {
                $msg .= ' Alasan: ' . $request->alasan_admin;
            }

            // Kirim notifikasi rejection via RTDB (realtime untuk Android)
            $firebaseService = app(FirebaseService::class);
            $firebaseService->notifyUnblockResponse(
                $user->id_user,
                'rejected',
                $msg
            );
            $firebaseService->notifyUserAccountEvent(
                $user->id_user,
                'unblock_rejected',
                $msg
            );

            // Kirim juga via FCM sebagai fallback
            if ($user->fcm_token) {
                $firebaseService->sendAdminNotification(
                    $user,
                    $msg,
                    'unblock_rejected'
                );
            }
        }

        $permintaan->save();
        return redirect()->back()->with('success', 'Permintaan unblock berhasil diproses');
    }

    public function hapusPermintaanUnblock($id)
    {
        $permintaan = PermintaanUnblockModel::findOrFail($id);
        $permintaan->delete();

        return redirect()->back()->with('success', 'Permintaan unblock berhasil dihapus');
    }

    public function bulkHapusPermintaanUnblock(Request $request)
    {
        $ids = $request->ids;
        if (!$ids || !is_array($ids)) {
            return redirect()->back()->with('error', 'Tidak ada data yang dipilih');
        }

        DB::transaction(function () use ($ids) {
            PermintaanUnblockModel::whereIn('id', $ids)->delete();
        });

        return redirect()->back()->with('success', count($ids) . ' Permintaan unblock berhasil dihapus');
    }

    public function verifyPassword(Request $request)
    {
        $request->validate([
            'password' => 'required',
        ]);

        if (Hash::check($request->password, Auth::user()->password)) {
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false, 'message' => 'Password salah!'], 401);
    }

    public function feedback()
    {
        $feedbacks = FeedbackModel::with('user')->orderBy('created_at', 'desc')->get();
        return view('admin.feedback', compact('feedbacks'));
    }

    public function markFeedbackRead($id)
    {
        $feedback = FeedbackModel::findOrFail($id);
        $feedback->is_read = 1;
        $feedback->save();

        return response()->json(['success' => true]);
    }
}
