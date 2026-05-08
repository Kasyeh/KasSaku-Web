@extends('template.masteru')

@section('content')
    <div class="max-w-4xl mx-auto space-y-8 animate-fade-in pb-20">
        
        {{-- Hero / Balance Card (Premium Glassmorphism) --}}
        <div class="relative overflow-hidden rounded-[32px] p-8 md:p-10 shadow-2xl">
            {{-- Dynamic Background Gradient --}}
            <div class="absolute inset-0 bg-gradient-to-br from-primary-500 via-primary-600 to-sky-600"></div>
            
            {{-- Abstract Aurora / Blob Overlay --}}
            <div class="absolute -top-24 -right-24 w-64 h-64 bg-white/20 blur-3xl rounded-full mix-blend-overlay"></div>
            <div class="absolute -bottom-24 -left-24 w-80 h-80 bg-sky-300/20 blur-3xl rounded-full mix-blend-overlay"></div>

            <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-end gap-6 text-white">
                <div class="space-y-4 w-full md:w-auto">
                    <div>
                        <p class="text-white/80 text-sm font-medium uppercase tracking-widest mb-1">Total Saldo</p>
                        <h2 class="text-4xl md:text-5xl font-black tracking-tight" style="font-family: 'Plus Jakarta Sans', sans-serif;">
                            Rp {{ number_format($balance->saldo ?? 0, 0, ',', '.') }}
                        </h2>
                    </div>
                    
                    <div class="flex items-center space-x-2 bg-white/10 backdrop-blur-md rounded-2xl px-4 py-2 w-fit border border-white/20">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        <p class="text-xs font-bold text-white/90">
                            Halo, {{ $user->username }}! 👋
                        </p>
                    </div>
                </div>

                {{-- Income / Expense Summary inside the Hero --}}
                <div class="flex items-center gap-4 w-full md:w-auto bg-black/10 backdrop-blur-md rounded-2xl p-4 border border-white/10">
                    <div>
                        <div class="flex items-center text-white/70 mb-1">
                            <span class="material-icons-round text-[14px] mr-1 text-emerald-300">arrow_downward</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider">Pemasukan</span>
                        </div>
                        <p class="text-sm font-bold">Rp {{ number_format($balance->pemasukan ?? 0, 0, ',', '.') }}</p>
                    </div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div>
                        <div class="flex items-center text-white/70 mb-1">
                            <span class="material-icons-round text-[14px] mr-1 text-rose-300">arrow_upward</span>
                            <span class="text-[10px] font-bold uppercase tracking-wider">Pengeluaran</span>
                        </div>
                        <p class="text-sm font-bold">Rp {{ number_format($balance->pengeluaran ?? 0, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>
        </div>
v>

        {{-- Quick Actions --}}
        <div>
            <h3 class="text-lg font-black text-slate-800 dark:text-white mb-4 ml-2">Aksi Cepat</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <a href="{{ url('user/statistik') }}?action=pemasukan" class="group relative overflow-hidden rounded-[24px] p-5 bg-white dark:bg-slate-800 border border-slate-100 dark:border-white/5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-emerald-50 dark:bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <span class="material-icons-round">add</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Pemasukan</h4>
                </a>
                
                <a href="{{ url('user/statistik') }}?action=pengeluaran" class="group relative overflow-hidden rounded-[24px] p-5 bg-white dark:bg-slate-800 border border-slate-100 dark:border-white/5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-rose-50 dark:bg-rose-500/10 text-rose-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <span class="material-icons-round">remove</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Pengeluaran</h4>
                </a>

                <a href="{{ url('user/impian') }}" class="group relative overflow-hidden rounded-[24px] p-5 bg-white dark:bg-slate-800 border border-slate-100 dark:border-white/5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-sky-50 dark:bg-sky-500/10 text-sky-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <span class="material-icons-round">star</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Buat Impian</h4>
                </a>

                <a href="{{ url('user/statistik') }}" class="group relative overflow-hidden rounded-[24px] p-5 bg-white dark:bg-slate-800 border border-slate-100 dark:border-white/5 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 text-center">
                    <div class="w-12 h-12 mx-auto rounded-full bg-amber-50 dark:bg-amber-500/10 text-amber-500 flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        <span class="material-icons-round">assessment</span>
                    </div>
                    <h4 class="text-xs font-bold text-slate-700 dark:text-slate-300">Dashboard</h4>
                </a>
            </div>
        </div>

        {{-- Feature Cards / Nav --}}
        <div>
            <h3 class="text-lg font-black text-slate-800 dark:text-white mb-4 ml-2">Menu Utama</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <a href="{{ url('user/riwayat') }}" class="card-premium hover-card rounded-[28px] p-7 block group border border-slate-100 dark:border-white/5">
                    <div class="w-14 h-14 rounded-[20px] bg-gradient-to-br from-primary-400 to-primary-600 text-white flex items-center justify-center mb-5 shadow-lg shadow-primary-500/30 group-hover:-translate-y-2 transition-transform duration-300">
                        <span class="material-icons-round text-2xl">history</span>
                    </div>
                    <h4 class="text-base font-black text-slate-800 dark:text-white mb-2">Riwayat Catatan</h4>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                        Kelola dan lihat rincian arus kas Anda. Pantau uang masuk dan keluar dengan mudah.
                    </p>
                </a>

                <a href="{{ url('user/impian') }}" class="card-premium hover-card rounded-[28px] p-7 block group border border-slate-100 dark:border-white/5">
                    <div class="w-14 h-14 rounded-[20px] bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center mb-5 shadow-lg shadow-emerald-500/30 group-hover:-translate-y-2 transition-transform duration-300">
                        <span class="material-icons-round text-2xl">stars</span>
                    </div>
                    <h4 class="text-base font-black text-slate-800 dark:text-white mb-2">Tabungan Impian</h4>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                        Wujudkan barang atau target impian Anda dengan sistem tabungan progresif yang rapi.
                    </p>
                </a>

                <a href="{{ url('user/statistik') }}" class="card-premium hover-card rounded-[28px] p-7 block group border border-slate-100 dark:border-white/5">
                    <div class="w-14 h-14 rounded-[20px] bg-gradient-to-br from-amber-400 to-amber-600 text-white flex items-center justify-center mb-5 shadow-lg shadow-amber-500/30 group-hover:-translate-y-2 transition-transform duration-300">
                        <span class="material-icons-round text-2xl">analytics</span>
                    </div>
                    <h4 class="text-base font-black text-slate-800 dark:text-white mb-2">Statistik & Laporan</h4>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-relaxed">
                        Evaluasi kesehatan finansial Anda melalui grafik dan ringkasan bulanan yang komprehensif.
                    </p>
                </a>
            </div>
        </div>

    </div>

    {{-- Welcome Overlay for Login --}}
    @if(session('show_welcome'))
        <div id="welcome-overlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm animate-fade-in transition-all duration-500">
            <div class="relative max-w-sm w-full mx-4 bg-white dark:bg-slate-800 rounded-[40px] shadow-2xl overflow-hidden animate-scale-up">
                {{-- Decorative background --}}
                <div class="absolute -top-12 -right-12 w-32 h-32 bg-primary-500/10 rounded-full blur-2xl"></div>
                <div class="absolute -bottom-12 -left-12 w-32 h-32 bg-sky-500/10 rounded-full blur-2xl"></div>

                <div class="absolute top-0 right-0 p-6">
                    <button onclick="closeWelcome()" class="w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 dark:bg-slate-700 text-slate-400 hover:text-slate-600 dark:hover:text-white transition-colors">
                        <span class="material-icons-round text-sm">close</span>
                    </button>
                </div>
                
                <div class="p-10 text-center">
                    <div class="relative inline-block mb-8">
                        <div class="absolute inset-0 bg-primary-500 blur-3xl opacity-20 rounded-full animate-pulse"></div>
                        @if(session('user_avatar'))
                            <img src="{{ session('user_avatar') }}" class="relative w-28 h-28 rounded-full border-4 border-white dark:border-slate-700 shadow-2xl mx-auto object-cover" alt="Avatar">
                        @else
                            <div class="relative w-28 h-28 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center border-4 border-white dark:border-slate-700 shadow-2xl mx-auto">
                                <span class="material-icons-round text-6xl text-white">person</span>
                            </div>
                        @endif
                    </div>
                    
                    <h3 class="text-slate-500 dark:text-slate-400 font-black text-xs uppercase tracking-[0.2em] mb-2">
                        {{ session('is_new_user') ? 'Selamat Datang di KasSaku!' : 'Selamat Datang Kembali,' }}
                    </h3>
                    <h2 class="text-3xl font-black text-slate-800 dark:text-white mb-4 tracking-tight">
                        {{ session('user_name', Auth::user()->username) }}
                    </h2>
                    <p class="text-slate-500 dark:text-slate-400 text-sm font-medium leading-relaxed mb-10 px-4">
                        {{ session('is_new_user') ? 'Mari mulai kelola keuanganmu dengan lebih pintar dan bijak bersama kami.' : 'Senang melihatmu lagi! Yuk cek catatan keuanganmu hari ini.' }}
                    </p>
                    
                    <button onclick="closeWelcome()" class="w-full py-4 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-black rounded-2xl shadow-xl shadow-primary-500/40 transition-all duration-300 hover:scale-[1.02] active:scale-95">
                        Lanjutkan Ke Dashboard
                    </button>
                </div>
            </div>
        </div>

        <script>
            function closeWelcome() {
                const overlay = document.getElementById('welcome-overlay');
                overlay.style.opacity = '0';
                overlay.querySelector('.animate-scale-up').style.transform = 'scale(0.9)';
                setTimeout(() => {
                    overlay.remove();
                }, 500);
            }
            
            // Auto close after 15 seconds
            setTimeout(() => {
                if(document.getElementById('welcome-overlay')) closeWelcome();
            }, 15000);
        </script>

        <style>
            .animate-scale-up {
                animation: scaleUp 0.6s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
            }
            @keyframes scaleUp {
                from { opacity: 0; transform: scale(0.7) translateY(20px); }
                to { opacity: 1; transform: scale(1) translateY(0); }
            }
        </style>
    @endif
@endsection