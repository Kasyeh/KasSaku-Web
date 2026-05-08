<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use App\Models\TransactionModel;
use App\Models\PermintaanUnblockModel;

class LoginController extends Controller
{


    public function login()
    {
        return view('login');
    }
    public function actionLogin(Request $request)
    {
        $credentials = $request->only('username', 'password');
        $remember = $request->has('remember');

        if (Auth::attempt($credentials, $remember)) {
            $user = Auth::user();

            if ($user->active == 0) {
                $userId = $user->id_user;
                Auth::logout();

                // Check for unblock requests
                $latestRequest = PermintaanUnblockModel::where('id_user', $userId)
                    ->orderBy('updated_at', 'desc')
                    ->first();

                if ($latestRequest) {
                    if ($latestRequest->status == 'pending') {
                        return redirect()->back()
                            ->with('blocked', true)
                            ->with('blocked_user_id', $userId)
                            ->with('pending_unblock', true)
                            ->with('error', 'Permintaan unblock Anda sedang ditinjau oleh Admin. Mohon tunggu informasi selanjutnya.');
                    } elseif ($latestRequest->status == 'ditolak') {
                        $reason = $latestRequest->alasan_admin ? ' Alasan: ' . $latestRequest->alasan_admin : '';
                        return redirect()->back()
                            ->with('blocked', true)
                            ->with('blocked_user_id', $userId)
                            ->with('rejected_unblock', true)
                            ->with('error', 'Permintaan unblock Anda telah ditolak oleh Admin.' . $reason . ' Anda dapat mengajukan permintaan baru.');
                    }
                }

                return redirect()->back()->with('blocked', true)->with('blocked_user_id', $userId)->with('error', 'Akun Anda sedang diblokir.');
            }

            if ($user->role === 'admin') {
                // Sync active status to Firebase RTDB so realtime listener has correct baseline
                try {
                    $firebaseService = app(\App\Services\FirebaseService::class);
                    $firebaseService->updateUserStatus($user->id_user, 1);
                } catch (\Exception $e) {
                    \Log::error('RTDB login status sync error: ' . $e->getMessage());
                }
                return redirect()->route('admin.dashboard');
            } else {
                // Sync active status to Firebase RTDB so realtime listener has correct baseline
                try {
                    $firebaseService = app(\App\Services\FirebaseService::class);
                    $firebaseService->updateUserStatus($user->id_user, 1);
                } catch (\Exception $e) {
                    \Log::error('RTDB login status sync error: ' . $e->getMessage());
                }

                return redirect()->route('homeUser');
            }
        }

        return redirect()->back()->withErrors([
            'login' => 'Username atau password salah!',
        ]);
    }
    public function actionLogout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        // Hapus semua session
        $request->session()->regenerateToken();
        // Mencegah CSRF attack setelah logout
        return redirect('/')->with('alert', 'Anda telah logout.');
    }



}
