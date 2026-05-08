<div class="animate-slide-up">
    <div class="flex items-center justify-between mb-6 px-2">
        <div>
            <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">Batas Belanja per Jenis</h3>
            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mt-1">Atur limit nominal untuk setiap kategori transaksi</p>
        </div>
        <button onclick="showBudgetModal()"
            class="flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-xs font-black rounded-2xl shadow-lg shadow-primary-500/20 hover:shadow-xl hover:shadow-primary-500/30 transition-all active:scale-95">
            <span class="material-icons-round text-sm">add</span> Tambah Batas
        </button>
    </div>

    @if(isset($budgetKategori) && $budgetKategori->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($budgetKategori as $budget)
                @php
                    $colorClass = $budget->over ? 'rose' : ($budget->percentage >= 75 ? 'amber' : 'emerald');
                    $iconMap = [
                        'makanan' => 'restaurant', 'makan' => 'restaurant', 'food' => 'restaurant',
                        'transportasi' => 'directions_car', 'transport' => 'directions_car',
                        'belanja' => 'shopping_bag', 'shopping' => 'shopping_bag',
                        'hiburan' => 'sports_esports', 'entertainment' => 'sports_esports',
                        'kesehatan' => 'favorite', 'health' => 'favorite',
                        'pendidikan' => 'school', 'education' => 'school',
                        'tagihan' => 'receipt_long', 'bills' => 'receipt_long',
                        'utilitas' => 'power', 'utilities' => 'power',
                    ];
                    $icon = $iconMap[strtolower($budget->kategori)] ?? 'category';
                @endphp
                <div class="card-premium hover-card rounded-[32px] p-7 relative group overflow-hidden" data-budget-id="{{ $budget->id }}">
                    @if($budget->over)
                        <div class="absolute inset-0 bg-gradient-to-br from-rose-500/5 to-rose-500/10 rounded-[32px]"></div>
                    @endif

                    <div class="relative z-10">
                        <div class="flex items-center justify-between mb-5">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-2xl bg-{{ $colorClass }}-100 dark:bg-{{ $colorClass }}-900/30 flex items-center justify-center text-{{ $colorClass }}-600 group-hover:scale-110 transition-transform">
                                    <span class="material-icons-round">{{ $icon }}</span>
                                </div>
                                <div>
                                    <h4 class="text-sm font-black text-slate-800 dark:text-white">{{ ucfirst($budget->kategori) }}</h4>
                                    <p class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">{{ $budget->periode_label }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                                <button onclick="hapusBudget({{ $budget->id }}, '{{ ucfirst($budget->kategori) }}')"
                                    class="w-8 h-8 rounded-xl bg-rose-50 dark:bg-rose-900/20 flex items-center justify-center text-rose-500 hover:bg-rose-100 dark:hover:bg-rose-900/40 transition-colors">
                                    <span class="material-icons-round text-sm">delete</span>
                                </button>
                            </div>
                        </div>

                        <div class="flex items-end justify-between mb-4">
                            <div>
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Terpakai</p>
                                <p class="text-xl font-black text-{{ $colorClass }}-600 dark:text-{{ $colorClass }}-400">
                                    Rp {{ number_format($budget->spent, 0, ',', '.') }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest">Limit</p>
                                <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                                    Rp {{ number_format($budget->nominal, 0, ',', '.') }}
                                </p>
                            </div>
                        </div>

                        <div class="w-full h-2.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden">
                            <div class="h-full rounded-full transition-all duration-1000 bg-{{ $colorClass }}-500"
                                style="width: {{ $budget->percentage }}%"></div>
                        </div>

                        <div class="flex items-center justify-between mt-3">
                            <span class="text-[10px] font-bold text-{{ $colorClass }}-600 dark:text-{{ $colorClass }}-400">
                                {{ number_format($budget->percentage, 1) }}% terpakai
                            </span>
                            @if($budget->over)
                                <span class="px-2 py-0.5 bg-rose-500 text-white text-[8px] font-black rounded-full animate-pulse">OVER BUDGET</span>
                            @elseif($budget->percentage >= 75)
                                <span class="px-2 py-0.5 bg-amber-500 text-white text-[8px] font-black rounded-full">HAMPIR LIMIT</span>
                            @else
                                <span class="text-[10px] font-bold text-slate-400">
                                    Sisa Rp {{ number_format(max(0, $budget->nominal - $budget->spent), 0, ',', '.') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="card-premium rounded-[32px] p-12 text-center">
            <div class="w-16 h-16 rounded-3xl bg-primary-50 dark:bg-primary-900/20 flex items-center justify-center text-primary-500 mx-auto mb-4">
                <span class="material-icons-round text-3xl">pie_chart</span>
            </div>
            <h4 class="text-sm font-black text-slate-800 dark:text-white mb-1">Belum ada budget kategori</h4>
            <p class="text-xs text-slate-400 mb-6">Atur limit nominal per kategori untuk kontrol keuangan yang lebih baik</p>
            <button onclick="showBudgetModal()"
                class="px-6 py-3 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-xs font-black rounded-2xl shadow-lg shadow-primary-500/20 hover:shadow-xl transition-all active:scale-95">
                <span class="material-icons-round text-sm align-middle mr-1">add</span> Buat Batas Pertama
            </button>
        </div>
    @endif
</div>
