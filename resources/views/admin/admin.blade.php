@extends('template.master')

@section('page_title', 'Ringkasan Sistem 🚀')
@section('page_subtitle', 'Monitoring performa KasSaku secara real-time')

@section('content')
    <div class="space-y-10">
        <!-- Dashboard Header Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
            <div class="flex items-center gap-3 bg-white/50 dark:bg-white/5 backdrop-blur-sm px-5 py-3 rounded-2xl border border-slate-100 dark:border-white/5 shadow-sm">
                <span class="material-icons-round text-primary-500">calendar_today</span>
                <span class="text-sm font-bold text-slate-700 dark:text-slate-200">{{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}</span>
            </div>
        </div>

        <!-- High-Vibrancy Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Total User Card -->
            <div class="group relative overflow-hidden card-premium rounded-3xl p-6 hover-card">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary-500/10 rounded-full blur-2xl group-hover:bg-primary-500/20 transition-all"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-primary-500/10 flex items-center justify-center text-primary-600 mb-4 transform transition-transform group-hover:scale-110">
                        <span class="material-icons-round">groups</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Total Pengguna</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white mt-1">{{ number_format($totalUser) }}</h3>
                    <div class="mt-4 flex items-center text-[10px] font-bold text-primary-600 bg-primary-50 dark:bg-primary-900/10 px-2 py-1 rounded-lg w-fit">
                        <span class="material-icons-round text-xs mr-1">trending_up</span> Terverifikasi
                    </div>
                </div>
            </div>

            <!-- Total Pemasukan Card -->
            <div class="group relative overflow-hidden card-premium rounded-3xl p-6 hover-card">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-sky-500/10 rounded-full blur-2xl group-hover:bg-sky-500/20 transition-all"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-sky-500/10 flex items-center justify-center text-sky-600 mb-4 transform transition-transform group-hover:scale-110">
                        <span class="material-icons-round">south_west</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Uang Masuk Global</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">Rp {{ number_format($total_pemasukan, 0, ',', '.') }}</h3>
                    <div class="mt-4 flex items-center text-[10px] font-bold text-sky-600 bg-sky-50 dark:bg-sky-900/10 px-2 py-1 rounded-lg w-fit">
                        Terkumpul Keseluruhan
                    </div>
                </div>
            </div>

            <!-- Total Pengeluaran Card -->
            <div class="group relative overflow-hidden card-premium rounded-3xl p-6 hover-card">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-rose-500/10 rounded-full blur-2xl group-hover:bg-rose-500/20 transition-all"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-600 mb-6 transform transition-transform group-hover:scale-110">
                        <span class="material-icons-round text-3xl">trending_down</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 uppercase tracking-widest">Uang Keluar Global</p>
                    <h3 class="text-2xl font-black text-slate-800 dark:text-white mt-1">Rp {{ number_format($total_pengeluaran, 0, ',', '.') }}</h3>
                    <div class="mt-4 flex items-center text-[10px] font-bold text-rose-600 bg-rose-50 dark:bg-rose-900/10 px-2 py-1 rounded-lg w-fit">
                        Uang Keluar Pengguna
                    </div>
                </div>
            </div>

            <!-- Net Balance Card (The Premium One) -->
            <div class="group relative overflow-hidden bg-slate-900 dark:bg-surface-light rounded-3xl p-6 border-0 shadow-premium hover-card cursor-default">
                <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-primary-500/20 rounded-full blur-3xl transition-transform group-hover:scale-125"></div>
                <div class="relative z-10">
                    <div class="w-12 h-12 rounded-2xl bg-white/10 dark:bg-slate-900/10 flex items-center justify-center text-white dark:text-slate-900 mb-4">
                        <span class="material-icons-round">account_balance</span>
                    </div>
                    <p class="text-xs font-black text-slate-400 dark:text-slate-500 uppercase tracking-widest">Net Ecosystem Value</p>
                    <h3 class="text-2xl font-black text-white dark:text-slate-900 mt-1">Rp {{ number_format($saldo, 0, ',', '.') }}</h3>
                    <p class="text-[10px] font-bold text-primary-400 dark:text-primary-600 mt-2">Ready to scale 📈</p>
                </div>
            </div>
        </div>

        <!-- Recent Activity Section -->
        <div class="grid grid-cols-1 gap-8">
            <div class="card-premium rounded-[2rem] overflow-hidden">
                <div class="p-8 border-b border-slate-50 dark:border-white/5 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-500 dark:text-slate-400">
                            <span class="material-icons-round text-xl">receipt_long</span>
                        </div>
                        <h3 class="text-lg font-bold text-slate-800 dark:text-white">Catatan User Terbaru</h3>
                    </div>
                    <a href="{{ url('list_user') }}" class="text-xs font-black uppercase tracking-widest text-primary-600 hover:text-primary-700 transition-colors">Lihat semua catatan</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-white/5">
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Tanggal</th>
                                <th class="px-8 py-4 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Deskripsi</th>
                                <th class="px-8 py-4 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Tipe</th>
                                <th class="px-8 py-4 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Nominal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                            @foreach($transaksi_terbaru as $trx)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors">
                                    <td class="px-8 py-5">
                                        <span class="text-sm font-bold text-slate-700 dark:text-slate-300">{{ \Carbon\Carbon::parse($trx->tanggal)->format('d M Y') }}</span>
                                    </td>
                                    <td class="px-8 py-5">
                                        <span class="text-sm font-medium text-slate-500 dark:text-slate-400">{{ $trx->keterangan }}</span>
                                    </td>
                                    <td class="px-8 py-5 text-center">
                                        @if($trx->tipe == 'pemasukan')
                                            <span class="px-3 py-1 text-[10px] font-black bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-lg uppercase">Income</span>
                                        @else
                                            <span class="px-3 py-1 text-[10px] font-black bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 rounded-lg uppercase">Expense</span>
                                        @endif
                                    </td>
                                    <td class="px-8 py-5 text-right font-black {{ $trx->tipe == 'pemasukan' ? 'text-primary-600' : 'text-rose-600' }}">
                                        {{ $trx->tipe == 'pemasukan' ? '+' : '-' }} Rp {{ number_format($trx->nominal, 0, ',', '.') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection