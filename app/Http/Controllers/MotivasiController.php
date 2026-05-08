<?php

namespace App\Http\Controllers;

use App\Models\MotivasiModel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MotivasiController extends Controller
{
    // Tampilkan semua motivasi di admin
    public function motivasi()
    {
        $motivasi = MotivasiModel::latest()->get();
        return view('admin.motivasi', compact('motivasi'));
    }

    // Form tambah motivasi
    public function tambahMotivasi()
    {
        return view('admin.tambahMotivasi');
    }

    // Simpan motivasi baru
    public function simpanMotivasi(Request $request)
    {
        if ($request->tipe === 'text') {
            $validated = $request->validate([
                'tipe' => 'required|in:text,image',
                'isi' => 'required|string|max:2000',
            ]);

            MotivasiModel::create([
                'tipe' => 'text',
                'isi' => $validated['isi'],
            ]);
        } else {
            $validated = $request->validate([
                'tipe' => 'required|in:text,image',
                'foto' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
            ]);

            // simpan ke storage/app/public/motivasi
            $path = $request->file('foto')->store('motivasi', 'public');

            MotivasiModel::create([
                'tipe' => 'image',
                'foto' => $path,
            ]);
        }

        return redirect()->route('motivasi')->with('success', 'Motivasi berhasil ditambahkan.');
    }

    // Edit motivasi
    public function editMotivasi($id)
    {
        $motivasi = MotivasiModel::findOrFail($id);
        return view('admin.editMotivasi', compact('motivasi'));
    }

    // Update motivasi
    public function updateMotivasi(Request $request, $id)
    {
        $motivasi = MotivasiModel::findOrFail($id);

        if ($request->tipe === 'text') {
            $validated = $request->validate([
                'tipe' => 'required|in:text,image',
                'isi' => 'required|string|max:2000',
            ]);

            // hapus foto lama kalau ada
            if ($motivasi->tipe === 'image' && $motivasi->foto) {
                Storage::disk('public')->delete($motivasi->foto);
            }

            $motivasi->update([
                'tipe' => 'text',
                'isi' => $validated['isi'],
                'foto' => null,
            ]);
        } else {
            $validated = $request->validate([
                'tipe' => 'required|in:text,image',
                'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:6144',
            ]);

            $data = ['tipe' => 'image', 'isi' => null];

            if ($request->hasFile('foto')) {
                // hapus foto lama bila ada
                if ($motivasi->foto) {
                    Storage::disk('public')->delete($motivasi->foto);
                }

                // simpan foto baru
                $data['foto'] = $request->file('foto')->store('motivasi', 'public');
            }

            $motivasi->update($data);
        }

        return redirect()->route('motivasi')->with('success', 'Motivasi berhasil diperbarui.');
    }

    // Hapus motivasi
    public function hapusMotivasi($id)
    {
        $motivasi = MotivasiModel::findOrFail($id);

        if ($motivasi->tipe === 'image' && $motivasi->foto) {
            Storage::disk('public')->delete($motivasi->foto);
        }

        $motivasi->delete();

        return redirect()->route('motivasi')->with('success', 'Motivasi berhasil dihapus.');
    }

    // Tampilkan motivasi untuk slider di user
    public function showSlider()
    {
        $motivasi = MotivasiModel::all();
        return view('home', compact('motivasi'));
    }
}
