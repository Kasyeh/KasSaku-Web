<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Mail\OTPMail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;

class ForgotPasswordController extends Controller
{
    /**
     * Tampilan form lupa password (input email)
     */
    public function showForgotForm()
    {
        return view('auth.forgot-password');
    }

    /**
     * Kirim OTP ke email
     */
    public function sendOtp(Request $request)
    {
        $request->merge([
            'email' => $request->has('email') ? trim((string) $request->email) : '',
            'username' => $request->has('username') ? trim((string) $request->username) : '',
        ]);

        $request->validate([
            'username' => 'required|string|min:2|max:100',
            'email' => 'required|email',
        ]);

        $user = User::where('username', $request->username)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return back()
                ->withErrors(['email' => 'Username dan email tidak cocok dengan akun terdaftar.'])
                ->withInput();
        }
        
        // Generate 6 digit OTP
        $otp = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        // Simpan ke database
        DB::table('password_reset_otps')->updateOrInsert(
            ['email' => $request->email],
            [
                'otp' => $otp,
                'expires_at' => Carbon::now()->addMinutes(10),
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );

        // Kirim Email
        try {
            Mail::to($request->email)->send(new OTPMail($otp, $user->username));
            return redirect()->route('password.reset.form', ['email' => $request->email])
                ->with('success', 'Kode OTP telah dikirim ke email Anda.')
                ->with('password_reset_username', $request->username);
        } catch (\Exception $e) {
            return back()->withErrors(['email' => 'Gagal mengirim email. Silakan coba lagi nanti.']);
        }
    }

    /**
     * Tampilan form reset password (input OTP & password baru)
     */
    public function showResetForm(Request $request)
    {
        $email = $request->query('email') ?: $request->email;
        $username = session('password_reset_username');

        if (!$email || !$username) {
            return redirect()
                ->route('password.request')
                ->withErrors(['email' => 'Silakan minta OTP dari halaman lupa password terlebih dahulu.']);
        }

        return view('auth.reset-password', compact('email', 'username'));
    }

    /**
     * Proses reset password
     */
    public function resetPassword(Request $request)
    {
        $otpDigits = preg_replace('/\D/', '', (string) $request->input('otp', ''));
        $request->merge([
            'email' => $request->has('email') ? trim((string) $request->email) : '',
            'username' => $request->has('username') ? trim((string) $request->username) : '',
            'otp' => $otpDigits,
        ]);

        $request->validate([
            'username' => 'required|string|min:2|max:100',
            'email' => 'required|email',
            'otp' => 'required|string|size:6|regex:/^[0-9]{6}$/',
            'password' => 'required|string|min:8|confirmed',
        ], [
            'otp.regex' => 'Kode OTP harus 6 digit angka.',
        ]);

        $user = User::where('username', $request->username)
            ->where('email', $request->email)
            ->first();

        if (!$user) {
            return back()->withErrors(['otp' => 'Username dan email tidak cocok dengan akun terdaftar.']);
        }

        $resetData = DB::table('password_reset_otps')
            ->where('email', $request->email)
            ->where('otp', $request->otp)
            ->first();

        if (!$resetData) {
            return back()->withErrors(['otp' => 'Kode OTP tidak valid.']);
        }

        if (Carbon::parse($resetData->expires_at)->isPast()) {
            return back()->withErrors(['otp' => 'Kode OTP telah kadaluarsa. Silakan minta kode baru.']);
        }

        // Update Password
        $user->password = Hash::make($request->password);
        $user->save();

        // Hapus OTP
        DB::table('password_reset_otps')->where('email', $request->email)->delete();

        return redirect()->route('login')->with('success', 'Password berhasil diubah. Silakan login dengan password baru.');
    }
}
