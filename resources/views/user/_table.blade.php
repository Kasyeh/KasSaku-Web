<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50/50 dark:bg-white/5">
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Jenis</th>
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Catatan</th>
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipe</th>
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Nominal
                </th>
                <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-center">Aksi
                </th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
            @forelse($transaksi as $item)
                <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group">
                    <td class="px-8 py-6">
                        <div class="flex flex-col">
                            <span class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('d M Y') }}
                            </span>
                            <span class="text-[10px] font-medium text-slate-400">
                                {{ \Carbon\Carbon::parse($item->tanggal)->format('H:i') }}
                            </span>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <span
                            class="px-3 py-1 bg-slate-100 dark:bg-white/5 text-slate-700 dark:text-slate-200 text-[10px] font-black uppercase rounded-lg">
                            {{ ucfirst($item->kategori) }}
                        </span>
                    </td>
                    <td class="px-8 py-6 text-sm font-medium text-slate-400 italic">
                        {{ $item->keterangan ?? '-' }}
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-2">
                            <span
                                class="w-1.5 h-1.5 rounded-full {{ $item->tipe == 'pemasukan' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                            <span
                                class="text-[10px] font-black uppercase {{ $item->tipe == 'pemasukan' ? 'text-green-600' : 'text-red-500' }}">
                                {{ $item->tipe }}
                            </span>
                        </div>
                    </td>
                    <td
                        class="px-8 py-6 text-sm font-black text-right {{ $item->tipe == 'pemasukan' ? 'text-green-500' : 'text-red-500' }}">
                        {{ $item->tipe == 'pemasukan' ? '+' : '-' }}Rp {{ number_format($item->nominal, 0, ',', '.') }}
                    </td>
                    <td class="px-8 py-6 text-center">
                        <button onclick="confirmDelete('{{ $item->id_transaksi }}')"
                            class="p-2 text-slate-300 hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-500/10 rounded-xl transition-all">
                            <span class="material-icons-round text-sm">delete</span>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="px-8 py-20 text-center">
                        <span class="material-icons-round text-4xl text-slate-200 mb-4">search_off</span>
                        <p class="text-xs font-black text-slate-300 uppercase tracking-widest">Data tidak ditemukan</p>
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>

@if($transaksi->hasPages())
    <div id="pagination-links" class="p-8 border-t border-slate-50 dark:border-slate-800">
        {{ $transaksi->links('partials.pagination') }}
    </div>
@endif