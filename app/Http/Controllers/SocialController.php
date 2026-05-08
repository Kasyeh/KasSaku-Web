<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\FirebaseService;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class SocialController extends Controller
{
    protected $firebaseService;

    public function __construct(FirebaseService $firebaseService)
    {
        $this->firebaseService = $firebaseService;
    }

    public function redirectToGoogle()
    {
        return Socialite::driver('google')->redirect();
    }

    public function handleGoogleCallback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
            return $this->handleUser($googleUser);
        } catch (Exception $e) {
            Log::error('Google login error: ' . $e->getMessage(), ['exception' => $e]);
            return redirect()->route('login')->with('error', 'Terjadi kesalahan saat masuk menggunakan Google.');
        }
    }

    public function loginWithGoogleAndroid(\Illuminate\Http\Request $request)
    {
        $idToken = $request->input('id_token');
        $fcmToken = $request->input('fcm_token');

        if (!$idToken) {
            return response()->json(['success' => false, 'message' => 'ID Token required'], 400);
        }

        try {
            // Verify ID Token with Google API
            $response = \Illuminate\Support\Facades\Http::get("https://oauth2.googleapis.com/tokeninfo?id_token={$idToken}");
            
            if ($response->failed()) {
                return response()->json(['success' => false, 'message' => 'Invalid ID Token'], 401);
            }

            $payload = $response->json();
            
            // Create a dummy user object to reuse logic
            $googleUser = (object) [
                'id' => $payload['sub'],
                'email' => $payload['email'],
                'name' => $payload['name'] ?? 'User',
                'avatar' => $payload['picture'] ?? null,
            ];

            return $this->handleUserAndroid($googleUser, $fcmToken);

        } catch (Exception $e) {
            Log::error('Android Google login error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Server error during Google login'], 500);
        }
    }

    private function handleUser($googleUser)
    {
        $user = $this->getOrCreateUser($googleUser);

        if ($user->active == 0) {
            return redirect()->route('login')->with('error', 'Akun Anda sedang diblokir.');
        }

        Auth::login($user);

        try {
            $this->firebaseService->updateUserStatus($user->id_user, 1);
        } catch (Exception $e) {
            Log::error('RTDB status sync error: ' . $e->getMessage());
        }

        session()->flash('show_welcome', true);
        session()->flash('is_new_user', $user->wasRecentlyCreated);
        session()->flash('user_name', $user->username);
        session()->flash('user_avatar', $user->avatar);

        return redirect()->route('homeUser');
    }

    private function handleUserAndroid($googleUser, $fcmToken = null)
    {
        $user = $this->getOrCreateUser($googleUser);

        if ($user->active == 0) {
            return response()->json([
                'responseCode' => 403,
                'message' => 'Akun Anda sedang diblokir.',
                'content' => [
                    'id_user' => $user->id_user,
                    'pending_unblock' => (bool)$user->pending_unblock,
                    'rejected_unblock' => (bool)$user->rejected_unblock,
                    'rejected_message' => $user->rejected_message
                ]
            ], 403);
        }

        // Generate Sanctum token
        $user->tokens()->delete();
        $token = $user->createToken('android-google-token')->plainTextToken;
        
        // Save FCM token if provided
        if ($fcmToken) {
            $user->update(['fcm_token' => $fcmToken]);
        }

        Auth::login($user);

        // Sync status to Firebase RTDB
        try {
            $this->firebaseService->updateUserStatus($user->id_user, 1);
            $this->firebaseService->notifyUserAccountEvent(
                $user->id_user,
                'login',
                'Login Google Berhasil.',
                ['active' => 1]
            );
        } catch (Exception $e) {
            Log::error('RTDB login status sync error (Google): ' . $e->getMessage());
        }

        return response()->json([
            'response_code' => 200,
            'message' => 'Login Berhasil',
            'content' => [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'email' => $user->email,
                'role' => $user->role,
                'active' => $user->active,
                'avatar' => $user->avatar,
                'is_new_user' => (bool)$user->wasRecentlyCreated,
                'token' => $token 
            ]
        ]);
    }

    private function getOrCreateUser($googleUser)
    {
        $user = User::where('google_id', $googleUser->id)->first();

        if (!$user) {
            $user = User::where('email', $googleUser->email)->first();

            if ($user) {
                $user->update([
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                ]);
            } else {
                $user = User::create([
                    'username' => $this->generateUniqueUsername($googleUser->name),
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'avatar' => $googleUser->avatar,
                    'password' => \Illuminate\Support\Facades\Hash::make(\Illuminate\Support\Str::random(16)),
                    'role' => 'user',
                    'active' => 1,
                ]);

                // Initialize balance for new user
                if (class_exists(\App\Models\BalanceModel::class)) {
                    \App\Models\BalanceModel::create([
                        'id_user' => $user->id_user,
                        'saldo' => 0,
                        'pemasukan' => 0,
                        'pengeluaran' => 0,
                    ]);
                }
            }
        }

        return $user;
    }

    private function generateUniqueUsername($name)
    {
        $baseUsername = Str::slug($name, '');
        $username = $baseUsername;
        $counter = 1;
        
        while (User::where('username', $username)->exists()) {
            $username = $baseUsername . $counter;
            $counter++;
        }
        
        return $username;
    }
}
