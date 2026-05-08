@extends('template.master')

@section('page_title', 'Profil Pengguna 💎')
@section('page_subtitle', 'Analisis mendalam aktivitas finansial ' . $user->username)

@section('content')
    <div class="space-y-10">
        <!-- Page Header & Navigation -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-4">
                <a href="{{ url('list_user') }}"
                    class="w-10 h-10 flex items-center justify-center bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-xl text-slate-500 hover:text-primary-500 hover:shadow-glow transition-all">
                    <span class="material-icons-round">arrow_back</span>
                </a>
            </div>

            <div class="flex items-center gap-3">
                <div
                    class="px-5 py-3 bg-white dark:bg-white/5 border border-slate-100 dark:border-white/10 rounded-2xl shadow-sm">
                    <div class="flex items-center gap-3">
                        <div
                            class="w-10 h-10 rounded-full bg-primary-500/10 flex items-center justify-center text-primary-600 font-bold">
                            {{ strtoupper(substr($user->username, 0, 1)) }}
                        </div>
                        <div>
                            <div class="text-sm font-bold text-slate-800 dark:text-white">{{ $user->username }}</div>
                            <div class="text-[10px] font-medium text-slate-400 uppercase tracking-widest">ID:
                                #{{ $user->id_user }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-5 gap-4">
            <div class="card-premium rounded-3xl p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Status Akun</p>
                @if($user->active == 1)
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-full text-[10px] font-black uppercase tracking-tight">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                        Active
                    </span>
                @else
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 dark:bg-rose-900/30 text-rose-700 dark:text-rose-400 rounded-full text-[10px] font-black uppercase tracking-tight">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                        Blocked
                    </span>
                @endif
            </div>

            <div class="card-premium rounded-3xl p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Uang Tersedia</p>
                <div class="text-xl font-black text-slate-800 dark:text-white">
                    {{ formatRupiah($saldo->saldo ?? 0) }}
                </div>
            </div>

            <div class="card-premium rounded-3xl p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Total Catatan</p>
                <div class="text-xl font-black text-slate-800 dark:text-white">
                    {{ number_format($totalTransactions ?? 0, 0, ',', '.') }}
                </div>
                <p class="text-[10px] font-medium text-slate-400 mt-1">Semua waktu</p>
            </div>

            <div class="card-premium rounded-3xl p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Catatan Terakhir</p>
                @if($lastTransaction)
                    <div class="text-sm font-bold text-slate-800 dark:text-white">
                        {{ \Carbon\Carbon::parse($lastTransaction->tanggal)->translatedFormat('d M Y') }}
                    </div>
                    <div
                        class="text-sm font-black {{ $lastTransaction->tipe == 'pemasukan' ? 'text-primary-600' : 'text-rose-600' }}">
                        {{ $lastTransaction->tipe == 'pemasukan' ? '+' : '-' }} {{ formatRupiah($lastTransaction->nominal) }}
                    </div>
                    <p class="text-[10px] font-medium text-slate-400 uppercase tracking-wide mt-1">
                        {{ $lastTransaction->tipe == 'pemasukan' ? 'Income' : 'Expense' }}
                    </p>
                @else
                    <p class="text-sm font-bold text-slate-500">Belum ada transaksi</p>
                @endif
            </div>

            <div class="card-premium rounded-3xl p-6">
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3">Unblock Pending</p>
                @if($hasPendingUnblockRequest)
                    <div class="inline-flex items-center gap-2">
                        <span
                            class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-[10px] font-black uppercase tracking-tight">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                            Ada
                        </span>
                        <span class="text-xs font-black text-amber-600">
                            {{ $pendingUnblockCount }}
                        </span>
                    </div>
                @else
                    <span
                        class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 rounded-full text-[10px] font-black uppercase tracking-tight">
                        Tidak ada
                    </span>
                @endif
            </div>
        </div>

        <div class="card-premium rounded-[2rem] p-8">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                <div class="space-y-5 flex-1">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-indigo-500/10 flex items-center justify-center text-indigo-600">
                            <span class="material-icons-round text-xl">security</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-slate-800 dark:text-white">Panel Moderasi</h3>
                            <p class="text-xs text-slate-500 dark:text-slate-400">Ringkasan keamanan dan aksi cepat admin
                            </p>
                        </div>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        @if($hasFcmToken)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-100 dark:bg-primary-900/30 text-primary-700 dark:text-primary-400 rounded-full text-[10px] font-black uppercase tracking-tight">
                                <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                FCM Aktif
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 rounded-full text-[10px] font-black uppercase tracking-tight">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span>
                                FCM Tidak Tersedia
                            </span>
                        @endif

                        @if($hasPendingUnblockRequest)
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400 rounded-full text-[10px] font-black uppercase tracking-tight">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span>
                                Pending Unblock: {{ $pendingUnblockCount }}
                            </span>
                        @else
                            <span
                                class="inline-flex items-center gap-1.5 px-3 py-1 bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 rounded-full text-[10px] font-black uppercase tracking-tight">
                                Tidak Ada Pending Unblock
                            </span>
                        @endif
                    </div>

                    <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Moderasi Terakhir
                        </p>
                        @if($latestUnblockRequest)
                            @php
                                $statusLabel = strtoupper($latestUnblockRequest->status);
                                $statusClass = 'bg-slate-100 text-slate-700 dark:bg-white/10 dark:text-slate-300';
                                if ($latestUnblockRequest->status === 'pending') {
                                    $statusClass = 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-400';
                                } elseif ($latestUnblockRequest->status === 'dikabulkan') {
                                    $statusClass = 'bg-primary-100 text-primary-700 dark:bg-primary-900/30 dark:text-primary-400';
                                } elseif ($latestUnblockRequest->status === 'ditolak') {
                                    $statusClass = 'bg-rose-100 text-rose-700 dark:bg-rose-900/30 dark:text-rose-400';
                                }
                            @endphp

                            <div class="flex flex-wrap items-center gap-2 mb-2">
                                <span
                                    class="inline-flex px-3 py-1 rounded-full text-[10px] font-black uppercase tracking-tight {{ $statusClass }}">
                                    {{ $statusLabel }}
                                </span>
                                <span class="text-xs text-slate-500 dark:text-slate-400">
                                    Update:
                                    {{ \Carbon\Carbon::parse($latestUnblockRequest->updated_at)->translatedFormat('d M Y H:i') }}
                                </span>
                            </div>
                            @if(!empty($latestUnblockRequest->pesan))
                                <p class="text-xs text-slate-600 dark:text-slate-300">
                                    Pesan user: {{ \Illuminate\Support\Str::limit($latestUnblockRequest->pesan, 120) }}
                                </p>
                            @endif
                            @if(!empty($latestUnblockRequest->alasan_admin))
                                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">
                                    Alasan admin: {{ \Illuminate\Support\Str::limit($latestUnblockRequest->alasan_admin, 120) }}
                                </p>
                            @endif
                        @else
                            <p class="text-sm font-medium text-slate-500">Belum ada riwayat permintaan unblock.</p>
                        @endif
                    </div>
                </div>

                <div class="w-full lg:w-auto flex lg:flex-col gap-3">
                    @if($user->active == 1)
                        <form action="{{ route('admin.user.block', $user->id_user) }}" method="POST" class="w-full lg:w-44">
                            @csrf
                            <button type="submit" onclick="return confirm('Blokir user ini sekarang?')"
                                class="w-full px-4 py-3 rounded-xl bg-amber-500 text-white text-sm font-bold hover:bg-amber-600 transition-colors">
                                Block User
                            </button>
                        </form>
                    @else
                        <form action="{{ route('admin.user.unblock', $user->id_user) }}" method="POST" class="w-full lg:w-44">
                            @csrf
                            <button type="submit" onclick="return confirm('Aktifkan kembali user ini?')"
                                class="w-full px-4 py-3 rounded-xl bg-indigo-500 text-white text-sm font-bold hover:bg-indigo-600 transition-colors">
                                Unblock User
                            </button>
                        </form>
                    @endif

                    <a href="{{ route('admin.permintaan_unblock') }}"
                        class="w-full lg:w-44 px-4 py-3 rounded-xl border border-slate-200 dark:border-white/10 text-sm font-bold text-slate-700 dark:text-slate-200 text-center hover:bg-slate-50 dark:hover:bg-white/5 transition-colors">
                        Lihat Antrian Unblock
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Current Balance -->
            <div class="group relative overflow-hidden card-premium rounded-[2rem] p-8 hover-card">
                <div
                    class="absolute -right-6 -top-6 w-32 h-32 bg-primary-500/10 rounded-full blur-3xl group-hover:bg-primary-500/20 transition-all">
                </div>
                <div class="relative z-10">
                    <div
                        class="w-14 h-14 rounded-2xl bg-primary-500/10 flex items-center justify-center text-primary-600 mb-6 transform transition-transform group-hover:scale-110">
                        <span class="material-icons-round text-3xl">account_balance_wallet</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Uang Tersedia</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                        {{ formatRupiah($saldo->saldo ?? 0) }}
                    </h3>
                    <div class="mt-6 h-1 w-full bg-slate-100 dark:bg-white/5 rounded-full overflow-hidden">
                        <div class="h-full bg-primary-500 w-3/4 rounded-full"></div>
                    </div>
                </div>
            </div>

            <!-- Monthly Income -->
            <div class="group relative overflow-hidden card-premium rounded-[2rem] p-8 hover-card">
                <div
                    class="absolute -right-6 -top-6 w-32 h-32 bg-emerald-500/10 rounded-full blur-3xl group-hover:bg-emerald-500/20 transition-all">
                </div>
                <div class="relative z-10">
                    <div
                        class="w-14 h-14 rounded-2xl bg-emerald-500/10 flex items-center justify-center text-emerald-600 mb-6 transform transition-transform group-hover:scale-110">
                        <span class="material-icons-round text-3xl">trending_up</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Uang Masuk Bulan Ini</p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                        {{ formatRupiah($monthlyPemasukan ?? 0) }}
                    </h3>
                    <p class="text-[10px] font-bold text-emerald-600 mt-4 flex items-center gap-1">
                        <span class="material-icons-round text-xs">arrow_circle_up</span> Real-time data
                    </p>
                </div>
            </div>

            <!-- Monthly Expense -->
            <div class="group relative overflow-hidden card-premium rounded-[2rem] p-8 hover-card">
                <div
                    class="absolute -right-6 -top-6 w-32 h-32 bg-rose-500/10 rounded-full blur-3xl group-hover:bg-rose-500/20 transition-all">
                </div>
                <div class="relative z-10">
                    <div
                        class="w-14 h-14 rounded-2xl bg-rose-500/10 flex items-center justify-center text-rose-600 mb-6 transform transition-transform group-hover:scale-110">
                        <span class="material-icons-round text-3xl">trending_down</span>
                    </div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Uang Keluar Bulan Ini
                    </p>
                    <h3 class="text-3xl font-black text-slate-800 dark:text-white tracking-tight">
                        {{ formatRupiah($monthlyPengeluaran ?? 0) }}
                    </h3>
                    <p class="text-[10px] font-bold text-rose-600 mt-4 flex items-center gap-1">
                        <span class="material-icons-round text-xs">arrow_circle_down</span> Controlled flow
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Dream Items Section -->
            <div class="lg:col-span-1 space-y-6">
                <div class="card-premium rounded-[2rem] overflow-hidden">
                    <div class="p-8 border-b border-slate-50 dark:border-white/5 flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-amber-500/10 flex items-center justify-center text-amber-600">
                            <span class="material-icons-round text-xl">star</span>
                        </div>
                        <h3 class="text-lg font-extrabold text-slate-800 dark:text-white">Tabungan Impian</h3>
                    </div>
                    <div class="p-8">
                        @if($dreamItems->count())
                            <div class="space-y-4">
                                @foreach($dreamItems as $item)
                                    <div
                                        class="p-4 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5 group hover:border-primary-500/30 transition-all">
                                        <div class="flex justify-between items-start mb-2">
                                            <div
                                                class="text-sm font-bold text-slate-800 dark:text-white group-hover:text-primary-500 transition-colors">
                                                {{ $item->nama_barang }}</div>
                                            <span
                                                class="text-[10px] font-black text-primary-600 bg-primary-100 dark:bg-primary-900/20 px-2 py-1 rounded-lg">Target</span>
                                        </div>
                                        <div class="text-lg font-black text-slate-700 dark:text-slate-300">
                                            {{ formatRupiah($item->harga_barang) }}
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="flex flex-col items-center justify-center py-10 opacity-50">
                                <span class="material-icons-round text-5xl mb-3">auto_fix_off</span>
                                <p class="text-sm font-medium text-slate-500">Belum ada barang impian</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Transaction History Section -->
            <div class="lg:col-span-2">
                <div class="card-premium rounded-[2rem] overflow-hidden">
                    <div class="p-8 border-b border-slate-50 dark:border-white/5 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div
                                class="w-10 h-10 rounded-xl bg-slate-100 dark:bg-white/5 flex items-center justify-center text-slate-500 dark:text-slate-400">
                                <span class="material-icons-round text-xl">history</span>
                            </div>
                            <h3 class="text-lg font-extrabold text-slate-800 dark:text-white">Catatan Keuangan</h3>
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        @if($transactions->count())
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-slate-50/50 dark:bg-white/5">
                                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Tanggal</th>
                                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Jenis</th>
                                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Keterangan</th>
                                        <th class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest">
                                            Tipe</th>
                                        <th
                                            class="px-8 py-5 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                                            Nominal</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                                    @foreach($transactions as $trx)
                                        <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-colors group">
                                            <td class="px-8 py-6">
                                                <div class="flex flex-col">
                                                    <span class="text-sm font-bold text-slate-700 dark:text-slate-200">
                                                        {{ \Carbon\Carbon::parse($trx->tanggal)->format('d M Y') }}
                                                    </span>
                                                    <span class="text-[10px] font-medium text-slate-400">
                                                        {{ \Carbon\Carbon::parse($trx->tanggal)->format('H:i') }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="px-8 py-6">
                                                <span
                                                    class="px-3 py-1 bg-slate-100 dark:bg-white/5 text-slate-700 dark:text-slate-200 text-[10px] font-black uppercase rounded-lg">
                                                    {{ ucfirst($trx->kategori) }}
                                                </span>
                                            </td>
                                            <td class="px-8 py-6 text-sm font-medium text-slate-400 italic">
                                                {{ $trx->keterangan ?? '-' }}
                                            </td>
                                            <td class="px-8 py-6">
                                                <div class="flex items-center gap-2">
                                                    <span
                                                        class="w-1.5 h-1.5 rounded-full {{ $trx->tipe == 'pemasukan' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                                                    <span
                                                        class="text-[10px] font-black uppercase {{ $trx->tipe == 'pemasukan' ? 'text-green-600' : 'text-red-500' }}">
                                                        {{ $trx->tipe }}
                                                    </span>
                                                </div>
                                            </td>
                                            <td
                                                class="px-8 py-6 text-sm font-black text-right {{ $trx->tipe == 'pemasukan' ? 'text-green-500' : 'text-red-500' }}">
                                                {{ $trx->tipe == 'pemasukan' ? '+' : '-' }}Rp
                                                {{ number_format($trx->nominal, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        @else
                            <div class="flex flex-col items-center justify-center py-20 opacity-50">
                                <div
                                    class="w-20 h-20 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center mb-4">
                                    <span class="material-icons-round text-4xl text-slate-400">receipt_long</span>
                                </div>
                                <p class="text-sm font-bold text-slate-500">Belum ada riwayat transaksi</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection