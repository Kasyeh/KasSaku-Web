<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\BalanceModel;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register()
    {
        return view('register');
    }

    public function actionRegister(Request $request)
    {
        // Validasi awal: username, email (opsional), dan password
        $validated = $request->validate([
            'username' => 'required|string|max:50|unique:users,username',
            'email' => 'required|email|unique:users,email',
            'password' => [
                'required',
                'string',
                'min:8',
                'regex:/[A-Z]/',      // minimal 1 huruf kapital
                'regex:/[0-9]/',      // minimal 1 angka
                'regex:/[!@#$%^&*_]/', // minimal 1 simbol dari set !@#$%^&*_
                'not_regex:/\s/',      // tidak boleh ada spasi
            ],
        ], [
            'username.required' => 'Username wajib diisi.',
            'username.unique' => 'Username sudah digunakan, coba nama lain.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah digunakan.',
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.required' => 'Password wajib diisi.',
            'password.regex' => 'Password harus mengandung huruf kapital, angka, simbol, dan tidak mengandung spasi.',
            'password.not_regex' => 'Password tidak boleh mengandung spasi.',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'user',
            'active' => 1,
        ]);

        // AUTO CREATE saldo user baru
        BalanceModel::create([
            'id_user' => $user->id_user,
            'saldo' => 0,
            'pemasukan' => 0,
            'pengeluaran' => 0,
        ]);

        return redirect('/')->with('registered_success', 'Pendaftaran berhasil! Silakan login.');
    }
}
