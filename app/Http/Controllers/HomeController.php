<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class HomeController extends Controller
{
    public function getSaldo($id_user)
    {
        $user = User::select(
            'users.id_user',
            'users.username',
            'tb_saldo_user.saldo'
        )
            ->leftJoin('tb_saldo_user', 'tb_saldo_user.id_user', '=', 'users.id_user')
            ->where('users.id_user', $id_user)
            ->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found'
            ], 404);
        }

        // Hitung Pemasukan dan Pengeluaran BULAN INI
        $pemasukanBulanIni = \App\Models\TransactionModel::where('id_user', $id_user)
            ->where('tipe', 'pemasukan')
            ->whereMonth('tanggal', \Carbon\Carbon::now()->month)
            ->whereYear('tanggal', \Carbon\Carbon::now()->year)
            ->sum('nominal');

        $pengeluaranBulanIni = \App\Models\TransactionModel::where('id_user', $id_user)
            ->where('tipe', 'pengeluaran')
            ->whereMonth('tanggal', \Carbon\Carbon::now()->month)
            ->whereYear('tanggal', \Carbon\Carbon::now()->year)
            ->sum('nominal');

        return response()->json([
            'success' => true,
            'message' => ($user->saldo == 0) ? 'Saldo masih kosong' : 'Data saldo berhasil diambil',
            'data' => [
                'id_user' => $user->id_user,
                'username' => $user->username,
                'saldo' => $user->saldo,
                'pemasukan' => $pemasukanBulanIni,
                'pengeluaran' => $pengeluaranBulanIni
            ]
        ]);
    }
}
