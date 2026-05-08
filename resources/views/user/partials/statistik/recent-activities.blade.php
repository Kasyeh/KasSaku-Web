<div class="card-premium hover-card rounded-[40px] p-10">
    <div class="flex items-center justify-between mb-8">
        <h3 class="text-xs font-black text-slate-400 uppercase tracking-widest">Aktivitas</h3>
        <a href="{{ url('user/riwayat') }}"
            class="text-[10px] font-black text-primary-600 uppercase transition-all hover:tracking-widest">Lihat
            Semua</a>
    </div>
    <div class="space-y-6">
        @forelse($transaksi as $item)
            <div class="flex items-center justify-between group">
                <div class="flex items-center gap-4">
                    <div
                        class="w-10 h-10 rounded-2xl flex items-center justify-center transition-all group-hover:scale-110 {{ $item->tipe == 'pemasukan' ? 'bg-emerald-100 text-emerald-600 dark:bg-emerald-900/30 dark:text-emerald-400' : 'bg-rose-100 text-rose-600 dark:bg-rose-900/30 dark:text-rose-400' }}">
                        <span class="material-icons-round text-lg">{{ $item->tipe == 'pemasukan' ? 'add' : 'remove' }}</span>
                    </div>
                    <div>
                        <p class="text-sm font-bold text-slate-800 dark:text-white">{{ ucfirst($item->kategori) }}</p>
                        <p class="text-[10px] font-medium text-slate-400">
                            {{ \Carbon\Carbon::parse($item->tanggal)->format('d M • H:i') }}
                        </p>
                    </div>
                </div>
                <p class="text-sm font-black {{ $item->tipe == 'pemasukan' ? 'text-emerald-500' : 'text-rose-500' }}">
                    {{ $item->tipe == 'pemasukan' ? '+' : '-' }}{{ number_format($item->nominal, 0, ',', '.') }}
                </p>
            </div>
        @empty
            <div class="text-center py-10 opacity-30">
                <span class="material-icons-round text-4xl mb-2">history</span>
                <p class="text-[10px] font-black uppercase">Belum ada data</p>
            </div>
        @endforelse
    </div>
</div>
