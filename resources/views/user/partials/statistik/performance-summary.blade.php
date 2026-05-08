@php
    $incDiff = $monthlyPemasukan - $prevMonthPemasukan;
    $expDiff = $monthlyPengeluaran - $prevMonthPengeluaran;
@endphp

<div class="glass-effect hover-card rounded-[40px] p-10">
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest text-white/50 dark:text-slate-400">Progress Performa</h3>
        <span class="material-icons-round text-slate-300">speed</span>
    </div>

    <div class="space-y-6">
        <div>
            <div class="flex justify-between text-xs mb-2">
                <span class="font-bold text-slate-500">Uang Masuk vs Lalu</span>
                <span class="{{ $incDiff >= 0 ? 'text-emerald-500' : 'text-rose-500' }} font-black">
                    {{ $incDiff >= 0 ? '↑' : '↓' }} {{ number_format(abs($incDiff), 0, ',', '.') }}
                </span>
            </div>
            <div class="w-full h-2 bg-slate-50 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-primary-500 rounded-full" style="width: {{ $prevMonthPemasukan > 0 ? min(100, ($monthlyPemasukan / $prevMonthPemasukan) * 100) : 100 }}%"></div>
            </div>
        </div>

        <div>
            <div class="flex justify-between text-xs mb-2">
                <span class="font-bold text-slate-500">Uang Keluar vs Lalu</span>
                <span class="{{ $expDiff <= 0 ? 'text-green-500' : 'text-red-500' }} font-black">
                    {{ $expDiff >= 0 ? '↑' : '↓' }} {{ number_format(abs($expDiff), 0, ',', '.') }}
                </span>
            </div>
            <div class="w-full h-2 bg-slate-50 dark:bg-slate-800 rounded-full overflow-hidden">
                <div class="h-full bg-red-400 rounded-full" style="width: {{ $prevMonthPengeluaran > 0 ? min(100, ($monthlyPengeluaran / $prevMonthPengeluaran) * 100) : 100 }}%"></div>
            </div>
        </div>
    </div>

    <div class="mt-8 pt-8 border-t border-slate-50 dark:border-slate-800/50">
        <p class="text-[10px] font-medium text-slate-400 text-center leading-relaxed italic">
            "{{ $trend == 'Meningkat' ? 'Keuanganmu menunjukkan tren positif bulan ini. Teruskan kebiasaan baikmu!' : 'Awasi pengeluaranmu bulan ini agar tetap sesuai rencana.' }}"
        </p>
    </div>
</div>
