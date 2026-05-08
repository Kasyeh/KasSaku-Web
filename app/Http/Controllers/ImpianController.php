<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ImpianModel;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use App\Services\ImpianProgressService;
use Illuminate\Validation\ValidationException;

class ImpianController extends Controller
{
    public function impianUser(Request $request)
    {
        // Query dasar: hanya ambil impian milik user yang login
        $query = ImpianModel::where('id_user', Auth::id());

        // Filter berdasarkan periode jika ada
        if ($request->has('periode') && !empty($request->periode)) {
            $periode = $request->periode;

            switch ($periode) {
                case 'hari_ini':
                    $query->whereDate('created_at', today());
                    break;
                case 'minggu_ini':
                    $query->whereBetween('created_at', [
                        now()->startOfWeek(),
                        now()->endOfWeek()
                    ]);
                    break;
                case 'bulan_ini':
                    $query->whereMonth('created_at', now()->month)
                        ->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Order by terbaru dan paginate
        $impian = $query->orderBy('created_at', 'desc')->paginate(10);
        $impian->getCollection()->transform(function ($item) {
            return ImpianProgressService::attachProgressToDream($item);
        });

        // Fetch user balance
        $balance = \App\Models\BalanceModel::where('id_user', Auth::id())->first();
        $saldo = $balance ? $balance->saldo : 0;

        // Jika request AJAX, return hanya table partial
        if ($request->ajax()) {
            return view('user._impian', compact('impian', 'saldo'));
        }

        return view('user.impian', compact('impian', 'saldo'));
    }

    public function tambahImpian()
    {
        return view('user.inputImpian');
    }

    public function simpanImpian(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_barang' => 'required|string|max:255',
            'foto_barang' => 'nullable|image|mimes:jpg,jpeg,png|max:102400',
            'harga_barang' => 'required|numeric|min:1',
            'deadline' => 'required|date|after:today',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // Upload file foto jika ada
        $fotoPath = null;
        if ($request->hasFile('foto_barang')) {
            $fotoPath = $request->file('foto_barang')->store('impian', 'public');
        }

        // Simpan ke database
        ImpianModel::create([
            'id_user' => Auth::id(), // ambil otomatis id user yang login
            'nama_barang' => $request->nama_barang,
            'foto_barang' => $fotoPath,
            'harga_barang' => $request->harga_barang,
            'deadline' => $request->deadline,
            'keterangan' => $request->filled('keterangan') ? $request->keterangan : null,
        ]);

        return redirect('/user/impian')->with('success', 'Impian berhasil ditambahkan!');

    }

    public function hapusImpian(Request $request, $id_impian)
    {
        $request->validate([
            'password' => 'required'
        ]);

        if (!\Illuminate\Support\Facades\Hash::check($request->password, Auth::user()->password)) {
            return redirect()->back()->with('error', 'Password yang Anda masukkan salah.');
        }

        // cari impian milik user yang login
        $impian = ImpianModel::where('id_impian', $id_impian)
            ->where('id_user', Auth::id())
            ->first();

        if (!$impian) {
            return redirect('/user/impian')->with('error', 'Impian tidak ditemukan.');
        }

        // hapus file foto bila ada
        if (!empty($impian->foto_barang)) {
            // foto disimpan di disk public
            if (Storage::disk('public')->exists($impian->foto_barang)) {
                Storage::disk('public')->delete($impian->foto_barang);
            }
        }

        $impian->delete();

        return redirect('/user/impian')->with('success', 'Impian berhasil dihapus.');
    }

    public function setorImpian(Request $request, $id_impian)
    {
        $validated = $request->validate([
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'nullable|string|max:255',
        ]);

        try {
            ImpianProgressService::setoranImpian(
                Auth::id(),
                (int) $id_impian,
                (float) $validated['nominal'],
                $validated['keterangan'] ?? null
            );
        } catch (ValidationException $e) {
            return redirect()->back()->withErrors($e->errors())->withInput();
        }

        return redirect('/user/impian')->with('success', 'Setoran impian berhasil disimpan.');
    }
}
