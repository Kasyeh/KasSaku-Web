@extends('template.masteru')

@section('page_title', 'Tambah Impian Baru 🎯')
@section('page_subtitle', 'Tentukan target tabungan and wujudkan keinginanmu')

@section('content')
    <div class="max-w-3xl mx-auto animate-slide-up">
        <div
            class="bg-surface-light dark:bg-surface-dark rounded-[40px] p-12 shadow-card border border-slate-100 dark:border-slate-800 relative overflow-hidden">
            {{-- Decorative element --}}
            <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/5 rounded-bl-[100px] pointer-events-none"></div>

            <form action="{{ url('user/impian/simpan') }}" method="POST" enctype="multipart/form-data"
                class="space-y-10 relative z-10">
                @csrf

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Nama Barang
                        Impian</label>
                    <div class="relative group">
                        <span
                            class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">stars</span>
                        <input type="text" name="nama_barang" value="{{ old('nama_barang') }}"
                            placeholder="Misal: MacBook Air M3"
                            class="w-full bg-slate-50 dark:bg-slate-800/40 border-none rounded-3xl py-5 pl-14 pr-6 font-bold text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-white/10 focus:ring-2 focus:ring-primary-500/50 transition-all"
                            required>
                    </div>
                    @error('nama_barang') <p class="text-[10px] text-red-500 font-bold pl-2 uppercase tracking-wider">
                        {{ $message }}
                    </p> @enderror
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Estimasi Harga
                        (IDR)</label>
                    <div class="relative group">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-primary-500 font-black text-xl">Rp</span>
                        <input type="text" id="harga_display" placeholder="0"
                            class="w-full bg-slate-50 dark:bg-slate-800/40 border-none rounded-3xl py-6 pl-16 pr-8 font-black text-3xl text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/50 transition-all tracking-tighter"
                            required>
                        <input type="hidden" id="harga_barang" name="harga_barang" value="{{ old('harga_barang') }}">
                    </div>
                    @error('harga_barang') <p class="text-[10px] text-red-500 font-bold pl-2 uppercase tracking-wider">
                        {{ $message }}
                    </p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Target
                            Selesai</label>
                        <div class="relative group">
                            <span
                                class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">calendar_month</span>
                            <input type="date" name="deadline" value="{{ old('deadline') }}"
                                class="w-full bg-slate-50 dark:bg-slate-800/40 border-none rounded-2xl py-5 pl-14 pr-6 font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/50 transition-all"
                                required>
                        </div>
                        @error('deadline') <p class="text-[10px] text-red-500 font-bold pl-2 uppercase tracking-wider">
                            {{ $message }}
                        </p> @enderror
                    </div>

                    <div class="space-y-4">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Foto
                            Barang</label>
                        <div class="relative">
                            <input type="file" name="foto_barang" accept="image/*" id="foto_barang" class="hidden">
                            <label for="foto_barang"
                                class="w-full bg-slate-50 dark:bg-slate-800/40 border-2 border-dashed border-slate-100 dark:border-white/5 rounded-2xl py-4.5 px-6 flex items-center gap-3 cursor-pointer hover:border-primary-500 transition-all group">
                                <div
                                    class="w-8 h-8 rounded-xl bg-white dark:bg-slate-900 flex items-center justify-center text-slate-400 group-hover:text-primary-500 transition-colors">
                                    <span class="material-icons-round text-lg">image</span>
                                </div>
                                <span id="file-name" class="text-xs font-bold text-slate-400 truncate">Pilih
                                    Gambar...</span>
                            </label>
                        </div>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Keterangan
                        (Opsional)</label>
                    <div class="relative group">
                        <span
                            class="material-icons-round absolute left-5 top-5 text-slate-300 group-focus-within:text-primary-500 transition-colors">notes</span>
                        <textarea name="keterangan" rows="4" placeholder="Tambahkan catatan singkat untuk impian ini (boleh kosong)"
                            class="w-full bg-slate-50 dark:bg-slate-800/40 border-none rounded-3xl py-4 pl-14 pr-6 font-bold text-slate-800 dark:text-white placeholder:text-slate-300 dark:placeholder:text-white/10 focus:ring-2 focus:ring-primary-500/50 transition-all resize-none">{{ old('keterangan') }}</textarea>
                    </div>
                    @error('keterangan') <p class="text-[10px] text-red-500 font-bold pl-2 uppercase tracking-wider">
                        {{ $message }}
                    </p> @enderror
                </div>

                <div class="pt-6">
                    <button type="submit"
                        class="w-full py-6 bg-primary-600 hover:bg-primary-700 text-white font-black rounded-3xl shadow-xl shadow-primary-500/25 transition-all transform hover:-translate-y-1 active:scale-95 text-xs uppercase tracking-[0.3em] flex items-center justify-center gap-3">
                        <span class="material-icons-round">rocket_launch</span>
                        Simpan Target Impian
                    </button>
                    <a href="{{ url('user/impian') }}"
                        class="block w-full text-center mt-6 text-[10px] font-black text-slate-400 uppercase tracking-widest hover:text-primary-500 transition-all">Batal
                        and Kembali</a>
                </div>
            </form>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const hDisp = document.getElementById('harga_display');
            const hReal = document.getElementById('harga_barang');
            const fInput = document.getElementById('foto_barang');
            const fName = document.getElementById('file-name');

            const fmt = (v) => {
                let n = v.replace(/\D/g, "");
                return n ? new Intl.NumberFormat("id-ID").format(n) : "";
            };

            if (hReal.value) hDisp.value = fmt(hReal.value);

            hDisp.oninput = function () {
                let v = this.value.replace(/\D/g, "");
                this.value = fmt(v);
                hReal.value = v;
            };

            fInput.onchange = function () {
                if (this.files[0]) fName.innerText = this.files[0].name;
            };

        });
    </script>
@endsection
