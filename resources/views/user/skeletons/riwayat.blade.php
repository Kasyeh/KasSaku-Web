<div class="overflow-x-auto">
    <table class="w-full text-left border-collapse">
        <thead>
            <tr class="bg-slate-50/50 dark:bg-white/5 text-slate-400">
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest">Tanggal</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest">Kategori</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest">Keterangan</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest">Tipe</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-right">Nominal</th>
                <th class="px-8 py-5 text-[10px] font-black uppercase tracking-widest text-center">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
            @for($i = 0; $i < 5; $i++)
                <tr class="animate-pulse">
                    <td class="px-8 py-6">
                        <div class="skeleton h-4 w-20"></div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="skeleton h-6 w-24 rounded-lg"></div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="skeleton h-4 w-32"></div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="flex items-center gap-2">
                            <div class="skeleton w-2 h-2 rounded-full"></div>
                            <div class="skeleton h-3 w-16"></div>
                        </div>
                    </td>
                    <td class="px-8 py-6">
                        <div class="skeleton h-4 w-24 ml-auto"></div>
                    </td>
                    <td class="px-8 py-6 flex justify-center">
                        <div class="skeleton h-8 w-8 rounded-xl"></div>
                    </td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>