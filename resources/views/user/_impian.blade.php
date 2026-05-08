<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @forelse($impian as $item)
        <div
            class="bg-surface-light dark:bg-surface-dark rounded-[40px] shadow-card border border-slate-100 dark:border-slate-800 overflow-hidden group hover:-translate-y-2 transition-all duration-500 relative">

            {{-- Image Section --}}
            <div class="h-64 overflow-hidden relative">
                @if($item->foto_barang)
                    <img src="{{ asset('storage/' . $item->foto_barang) }}"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700"
                        alt="{{ $item->nama_barang }}">
                @else
                    <div class="w-full h-full bg-slate-50 dark:bg-white/5 flex flex-col items-center justify-center">
                        <span class="material-icons-round text-6xl text-slate-200 dark:text-slate-800">stars</span>
                    </div>
                @endif

                {{-- Floating Actions --}}
                <div
                    class="absolute top-6 right-6 flex flex-col gap-3 opacity-0 group-hover:opacity-100 translate-x-4 group-hover:translate-x-0 transition-all duration-300">
                    <button
                        class="btn-setor-impian w-12 h-12 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded-2xl flex items-center justify-center text-emerald-500 shadow-xl hover:bg-emerald-500 hover:text-white transition-all transform hover:scale-110"
                        data-id="{{ $item->id_impian }}" data-nama="{{ e($item->nama_barang) }}"
                        data-sisa="{{ (int) ($item->sisa_target ?? $item->harga_barang) }}" title="Tambah Tabungan">
                        <span class="material-icons-round">paid</span>
                    </button>
                    <button
                        class="btn-detail-impian w-12 h-12 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded-2xl flex items-center justify-center text-primary-500 shadow-xl hover:bg-primary-500 hover:text-white transition-all transform hover:scale-110"
                        data-nama="{{ e($item->nama_barang) }}"
                        data-foto="{{ $item->foto_barang ? asset('storage/' . $item->foto_barang) : '' }}"
                        data-harga="{{ $item->harga_barang }}"
                        data-deadline="{{ \Carbon\Carbon::parse($item->deadline)->format('d M Y') }}"
                        data-keterangan="{{ e($item->keterangan ?? '') }}">
                        <span class="material-icons-round">visibility</span>
                    </button>
                    <form action="{{ url('/user/impian/hapus/' . $item->id_impian) }}" method="POST"
                        class="form-hapus-impian">
                        @csrf
                        <input type="hidden" name="password" class="input-password-hapus">
                        <button type="submit"
                            class="w-12 h-12 bg-white/90 dark:bg-slate-900/90 backdrop-blur rounded-2xl flex items-center justify-center text-red-500 shadow-xl hover:bg-red-500 hover:text-white transition-all transform hover:scale-110">
                            <span class="material-icons-round">delete</span>
                        </button>
                    </form>
                </div>
            </div>

            {{-- Info Section --}}
            <div class="p-10">
                <div class="flex items-center justify-between mb-2">
                    <span
                        class="px-3 py-1 bg-primary-50 dark:bg-primary-900/20 text-primary-600 dark:text-primary-400 rounded-lg text-[9px] font-black uppercase tracking-widest">
                        Target Menabung
                    </span>
                    <span
                        class="text-[10px] font-bold text-slate-300">{{ \Carbon\Carbon::parse($item->deadline)->format('Y') }}</span>
                </div>
                <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-4 tracking-tight truncate">
                    {{ $item->nama_barang }}
                </h3>

                <div class="pt-6 border-t border-slate-50 dark:border-slate-800/50 flex flex-col gap-1">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Estimasi Biaya</p>
                    <div class="flex items-baseline gap-1">
                        <span class="text-sm font-bold text-primary-600">Rp</span>
                        <span
                            class="text-3xl font-black text-primary-600 dark:text-primary-400 tracking-tighter">{{ number_format($item->harga_barang, 0, ',', '.') }}</span>
                    </div>
                </div>

                @php
                    $danaTerkumpul = (int) ($item->dana_terkumpul ?? 0);
                    $sisaTarget = (int) ($item->sisa_target ?? max(0, ((int) $item->harga_barang) - $danaTerkumpul));
                    $progress = (float) ($item->persentase_progress ?? 0);
                    $isTercapai = (bool) ($item->is_tercapai ?? false);
                @endphp

                <div
                    class="mt-6 p-5 rounded-3xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-slate-800/60 space-y-3">
                    <div class="flex items-center justify-between">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Progress Nyata</p>
                        @if($isTercapai)
                            <span
                                class="px-2 py-1 rounded-full bg-emerald-500 text-white text-[8px] font-black uppercase tracking-widest">Target
                                Tercapai</span>
                        @else
                            <span
                                class="text-[10px] font-black text-primary-600 dark:text-primary-400">{{ number_format($progress, 1) }}%</span>
                        @endif
                    </div>
                    <div class="w-full h-2.5 bg-slate-200 dark:bg-slate-700 rounded-full overflow-hidden">
                        <div class="h-full {{ $isTercapai ? 'bg-emerald-500' : 'bg-primary-500' }} rounded-full transition-all duration-500"
                            style="width: {{ $progress }}%"></div>
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Sudah Terkumpul</p>
                            <p class="text-xs font-black text-emerald-600 dark:text-emerald-400">Rp
                                {{ number_format($danaTerkumpul, 0, ',', '.') }}</p>
                        </div>
                        <div>
                            <p class="text-[9px] font-black text-slate-400 uppercase tracking-wider">Masih Kurang</p>
                            <p class="text-xs font-black text-slate-600 dark:text-slate-300">Rp
                                {{ number_format($sisaTarget, 0, ',', '.') }}</p>
                        </div>
                    </div>
                    <p class="text-[9px] font-semibold text-slate-400 italic">
                        {{ $danaTerkumpul > 0 ? 'Tabungan terakhir sudah tercatat dan perkembangan diperbarui otomatis.' : 'Belum ada tabungan.' }}
                    </p>
                </div>

                <div class="mt-6 flex items-center text-slate-400 gap-2">
                    <span class="material-icons-round text-lg opacity-40">event_available</span>
                    <span
                        class="text-[10px] font-bold uppercase tracking-[0.1em]">{{ \Carbon\Carbon::parse($item->deadline)->format('d F Y') }}</span>
                </div>
            </div>
        </div>
    @empty
        <div
            class="col-span-full py-24 bg-slate-50 dark:bg-white/5 border-2 border-dashed border-slate-200 dark:border-slate-800/50 rounded-[50px] text-center flex flex-col items-center">
            <div
                class="w-32 h-32 bg-white dark:bg-surface-dark rounded-[40px] shadow-card flex items-center justify-center mb-8 transform -rotate-6">
                <span class="material-icons-round text-6xl text-slate-100 dark:text-slate-800">stars</span>
            </div>
            <h3 class="text-2xl font-black text-slate-800 dark:text-white mb-2">Mulai Daftar Impianmu</h3>
            <p class="text-sm font-medium text-slate-400 mb-10 max-w-sm mx-auto leading-relaxed">Jangan hanya sekedar
                membayangkan, ayo buat target menabung kmu sekarang!</p>
            <a href="{{ url('user/impian/tambah') }}"
                class="px-10 py-5 bg-primary-600 hover:bg-primary-700 text-white font-black rounded-2xl shadow-xl shadow-primary-500/30 transition-all active:scale-95 text-xs uppercase tracking-widest">
                Tambah Target Sekarang
            </a>
        </div>
    @endforelse
</div>