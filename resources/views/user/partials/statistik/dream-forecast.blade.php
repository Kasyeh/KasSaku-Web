@if(isset($impianList) && count($impianList) > 0)
    <div class="animate-slide-up">
        <div class="flex items-center justify-between mb-6 px-2">
            <div>
                <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">Perkiraan Tabungan</h3>
                <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Estimasi pencapaian berdasarkan rata tabungan</p>
            </div>
            <span class="material-icons-round text-primary-500 bg-primary-50 dark:bg-primary-900/20 p-2 rounded-xl">auto_graph</span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach($impianList as $impian)
                @php
                    $reachPerc = $impian->harga_barang > 0 ? min(100, ($avgSavings / $impian->harga_barang) * 100) : 0;
                @endphp
                <a href="{{ url('user/impian') }}" class="card-premium hover-card p-8 rounded-[40px] flex flex-col justify-between cursor-pointer focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-primary-500/50">
                    <div>
                        <div class="flex items-center gap-3 mb-4">
                            <div class="w-10 h-10 rounded-xl bg-slate-50 dark:bg-white/5 flex items-center justify-center">
                                <span class="material-icons-round text-primary-500">stars</span>
                            </div>
                            <div>
                                <h4 class="text-sm font-black text-slate-800 dark:text-white truncate max-w-[150px]">{{ $impian->nama_barang }}</h4>
                                <p class="text-[10px] font-bold text-slate-400">Rp {{ number_format($impian->harga_barang, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <div class="space-y-3">
                            <div class="flex justify-between items-end">
                                <span class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Kontribusi Bulanan</span>
                                <span class="text-xs font-black text-primary-600 dark:text-primary-400">{{ number_format($reachPerc, 1) }}%</span>
                            </div>
                            <div class="w-full h-2 bg-slate-50 dark:bg-slate-800 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full transition-all duration-1000" style="width: {{ $reachPerc }}%"></div>
                            </div>
                        </div>
                    </div>

                    <p class="mt-6 text-[9px] font-semibold text-slate-400 italic leading-relaxed">
                        Tabungan rata-rata Anda mencakup <span class="text-primary-600 font-bold">{{ number_format($reachPerc, 1) }}%</span> dari target ini setiap bulannya.
                    </p>
                </a>
            @endforeach
        </div>
    </div>
@endif
