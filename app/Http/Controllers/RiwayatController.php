<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Models\TransactionModel;
use Illuminate\Support\Facades\Auth;

class RiwayatController extends Controller
{
    public function riwayatUser(Request $request)
    {
        $userId = Auth::id();

        $query = TransactionModel::where('id_user', $userId);

        // Filter periode cepat
        if ($request->periode == 'hari_ini') {
            $query->whereDate('tanggal', Carbon::today());
        } elseif ($request->periode == 'minggu_ini') {
            $query->whereBetween('tanggal', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
        } elseif ($request->periode == 'bulan_ini') {
            $query->whereMonth('tanggal', Carbon::now()->month)
                ->whereYear('tanggal', Carbon::now()->year);
        }

        // Filter manual
        if ($request->tanggal) {
            $query->whereDate('tanggal', $request->tanggal);
        }
        if ($request->bulan) {
            $query->whereMonth('tanggal', $request->bulan);
        }
        if ($request->tahun) {
            $query->whereYear('tanggal', $request->tahun);
        }
        if ($request->tipe) {
            $query->where('tipe', $request->tipe);
        }
        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('kategori', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('keterangan', 'LIKE', '%' . $request->search . '%');
            });
        }

        // Pagination tetap pakai hasil query filter di atas
        $transaksi = $query->orderBy('created_at', 'desc')->paginate(10);

        $showPagination = true;

        if ($request->ajax()) {
            return view('user._table', compact('transaksi', 'showPagination'))->render();
        }

        return view('user.riwayat', compact('transaksi', 'showPagination'));
    }
}
