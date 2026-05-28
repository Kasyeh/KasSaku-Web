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
        {{-- Smart AI Nudges --}}
        @if(isset($nudges) && count($nudges) > 0)
        <div class="relative group/nudges animate-slide-up">
            <div class="absolute -inset-[1px] bg-gradient-to-r from-sky-400 via-indigo-500 to-purple-500 rounded-[32px] opacity-10 group-hover/nudges:opacity-25 transition-opacity duration-500 blur-sm"></div>
            
            <div class="relative bg-white dark:bg-slate-900 border border-slate-100 dark:border-white/5 rounded-[32px] p-6 shadow-sm hover:shadow-xl transition-all duration-300">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center shadow-lg shadow-indigo-500/20 text-white">
                        <span class="material-icons-round text-lg">psychology</span>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800 dark:text-white tracking-tight">AI Smart Nudges</h3>
                        <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.15em]">Asisten Keuangan Cerdas KasSaku</p>
                    </div>
                </div>

                <div class="space-y-4">
                    @foreach($nudges as $nudge)
                        @php
                            $bgMap = [
                                'warning' => 'bg-rose-50 dark:bg-rose-500/10 border-rose-100 dark:border-rose-500/20 text-rose-600 dark:text-rose-400',
                                'info' => 'bg-sky-50 dark:bg-sky-500/10 border-sky-100 dark:border-sky-500/20 text-sky-600 dark:text-sky-400',
                                'success' => 'bg-emerald-50 dark:bg-emerald-500/10 border-emerald-100 dark:border-emerald-500/20 text-emerald-600 dark:text-emerald-400',
                                'idle' => 'bg-purple-50 dark:bg-purple-500/10 border-purple-100 dark:border-purple-500/20 text-purple-600 dark:text-purple-400'
                            ];
                            $iconBgMap = [
                                'warning' => 'bg-rose-500/20 text-rose-500 dark:bg-rose-500/20 dark:text-rose-400',
                                'info' => 'bg-sky-500/20 text-sky-500 dark:bg-sky-500/20 dark:text-sky-400',
                                'success' => 'bg-emerald-500/20 text-emerald-500 dark:bg-emerald-500/20 dark:text-emerald-400',
                                'idle' => 'bg-purple-500/20 text-purple-500 dark:bg-purple-500/20 dark:text-purple-400'
                            ];
                            $type = $nudge['type'] ?? 'info';
                            $class = $bgMap[$type] ?? $bgMap['info'];
                            $iconClass = $iconBgMap[$type] ?? $iconBgMap['info'];
                        @endphp
                        
                        <div class="flex gap-4 p-4 rounded-2xl border {{ $class }} hover:scale-[1.01] transition-transform duration-200">
                            <div class="w-10 h-10 rounded-xl {{ $iconClass }} flex items-center justify-center shrink-0">
                                <span class="material-icons-round text-lg">{{ $nudge['icon'] ?? 'info' }}</span>
                            </div>
                            <div class="space-y-1">
                                <h4 class="text-xs font-bold text-slate-800 dark:text-white">{{ $nudge['title'] }}</h4>
                                <p class="text-xs text-slate-500 dark:text-slate-400 leading-relaxed">{{ $nudge['message'] }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- ✨ Motivation Carousel Section --}}
        @if(isset($motivasi) && $motivasi->count() > 0)
        <div class="motivation-carousel-wrapper relative group/carousel animate-slide-up" id="motivationCarousel">
            <div class="absolute -inset-[1px] bg-gradient-to-r from-amber-400 via-orange-500 to-rose-500 rounded-[32px] opacity-10 group-hover/carousel:opacity-25 transition-opacity duration-500 blur-sm"></div>

            <div class="relative bg-white dark:bg-slate-900 border border-slate-100 dark:border-white/5 rounded-[32px] overflow-hidden">
                {{-- Header --}}
                <div class="flex items-center justify-between px-8 pt-8 pb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center shadow-lg shadow-orange-500/20">
                            <span class="material-icons-round text-white text-lg">auto_awesome</span>
                        </div>
                        <div>
                            <h3 class="text-base font-black text-slate-800 dark:text-white tracking-tight">Motivasi</h3>
                            <p class="text-[9px] font-bold text-slate-400 uppercase tracking-[0.15em]">Inspirasi untuk perjalanan finansialmu</p>
                        </div>
                    </div>
                    @if($motivasi->count() > 1)
                    <div class="flex items-center gap-1.5">
                        <span class="text-[10px] font-black text-slate-300 dark:text-slate-600 tabular-nums">
                            <span id="slideCurrentNum">1</span><span class="mx-0.5">/</span>{{ $motivasi->count() }}
                        </span>
                    </div>
                    @endif
                </div>

                {{-- Slides Container --}}
                <div class="relative overflow-hidden" style="min-height: 200px;" id="slidesContainer">
                    @foreach($motivasi as $index => $item)
                    <div class="motivation-slide absolute inset-0 transition-all duration-600 ease-in-out {{ $index === 0 ? 'opacity-100 translate-x-0 visible' : 'opacity-0 translate-x-8 invisible' }}"
                         data-slide="{{ $index }}">

                        @if($item->tipe == 'image' && $item->foto)
                            {{-- Image Motivasi --}}
                            <div class="px-8 pb-4 h-full">
                                <div class="relative rounded-2xl overflow-hidden h-[180px] group/img">
                                    <img src="{{ asset('storage/' . $item->foto) }}"
                                         alt="Motivasi"
                                         class="w-full h-full object-cover transition-transform duration-700 group-hover/img:scale-105"
                                         loading="lazy">
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-black/10 to-transparent"></div>
                                    @if($item->isi)
                                    <div class="absolute bottom-0 left-0 right-0 p-6">
                                        <p class="text-white text-base font-bold leading-relaxed drop-shadow-lg line-clamp-2">
                                            "{{ $item->isi }}"
                                        </p>
                                    </div>
                                    @endif
                                </div>
                            </div>
                        @else
                            {{-- Text Motivasi --}}
                            <div class="flex items-center justify-center h-full px-8 pb-4">
                                <div class="max-w-2xl text-center py-4">
                                    <div class="relative inline-block">
                                        <span class="absolute -top-6 -left-4 text-7xl font-black text-primary-500/10 dark:text-primary-400/10 select-none leading-none">"</span>
                                        <p dir="auto" class="text-lg font-bold text-slate-700 dark:text-slate-200 leading-relaxed relative z-10 px-6" style="font-family: 'Amiri', 'Traditional Arabic', 'Arabic Typesetting', 'Plus Jakarta Sans', sans-serif; line-height: 1.8;">
                                            {{ $item->isi }}
                                        </p>
                                        <span class="absolute -bottom-10 -right-2 text-7xl font-black text-primary-500/10 dark:text-primary-400/10 select-none leading-none rotate-180">"</span>
                                    </div>
                                    <div class="mt-6 flex items-center justify-center gap-2">
                                        <div class="w-6 h-[2px] bg-primary-500/30 rounded-full"></div>
                                        <span class="text-[9px] font-black text-primary-500/50 uppercase tracking-[0.2em]">KasSaku</span>
                                        <div class="w-6 h-[2px] bg-primary-500/30 rounded-full"></div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                    @endforeach
                </div>

                {{-- Bottom Navigation --}}
                @if($motivasi->count() > 1)
                <div class="flex items-center justify-between px-8 pb-6">
                    <button onclick="prevSlide()"
                        class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 flex items-center justify-center text-slate-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 hover:border-primary-200 dark:hover:border-primary-800/30 transition-all active:scale-90"
                        aria-label="Sebelumnya">
                        <span class="material-icons-round text-lg">chevron_left</span>
                    </button>

                    <div class="flex items-center gap-2">
                        @foreach($motivasi as $index => $item)
                        <button
                            class="motivation-dot h-2 rounded-full transition-all duration-400 {{ $index === 0 ? 'bg-primary-500 w-7 shadow-sm shadow-primary-500/30' : 'bg-slate-200 dark:bg-slate-700 w-2 hover:bg-slate-300 dark:hover:bg-slate-600' }}"
                            data-slide="{{ $index }}"
                            onclick="goToSlide({{ $index }})"
                            aria-label="Slide {{ $index + 1 }}">
                        </button>
                        @endforeach
                    </div>

                    <button onclick="nextSlide()"
                        class="w-9 h-9 rounded-xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 flex items-center justify-center text-slate-400 hover:text-primary-500 hover:bg-primary-50 dark:hover:bg-primary-900/10 hover:border-primary-200 dark:hover:border-primary-800/30 transition-all active:scale-90"
                        aria-label="Berikutnya">
                        <span class="material-icons-round text-lg">chevron_right</span>
                    </button>
                </div>
                @endif
            </div>
        </div>
        @endif


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

    <script>
        document.addEventListener("DOMContentLoaded", () => {
            // Carousel Logic
            let currentSlide = 0;
            const slides = document.querySelectorAll('.motivation-slide');
            const dots = document.querySelectorAll('.motivation-dot');
            const totalSlides = slides.length;
            const counterEl = document.getElementById('slideCurrentNum');
            let autoplayTimer = null;

            function updateSlide(newIndex, direction = 'next') {
                if (newIndex === currentSlide || newIndex < 0 || newIndex >= totalSlides) return;

                const outSlide = slides[currentSlide];
                const inSlide = slides[newIndex];

                const outTranslate = direction === 'next' ? '-translate-x-8' : 'translate-x-8';
                const inStartTranslate = direction === 'next' ? 'translate-x-8' : '-translate-x-8';

                outSlide.classList.remove('opacity-100', 'translate-x-0', 'visible');
                outSlide.classList.add('opacity-0', outTranslate, 'invisible');

                inSlide.classList.remove('opacity-0', 'translate-x-8', '-translate-x-8', 'invisible');
                inSlide.classList.add(inStartTranslate);

                void inSlide.offsetWidth;

                requestAnimationFrame(() => {
                    inSlide.classList.remove(inStartTranslate);
                    inSlide.classList.add('opacity-100', 'translate-x-0', 'visible');
                });

                if (dots[currentSlide]) {
                    dots[currentSlide].classList.remove('bg-primary-500', 'w-7', 'shadow-sm', 'shadow-primary-500/30');
                    dots[currentSlide].classList.add('bg-slate-200', 'dark:bg-slate-700', 'w-2');
                }
                if (dots[newIndex]) {
                    dots[newIndex].classList.remove('bg-slate-200', 'dark:bg-slate-700', 'w-2');
                    dots[newIndex].classList.add('bg-primary-500', 'w-7', 'shadow-sm', 'shadow-primary-500/30');
                }

                if (counterEl) counterEl.textContent = newIndex + 1;

                currentSlide = newIndex;
            }

            window.goToSlide = function(index) {
                const direction = index > currentSlide ? 'next' : 'prev';
                updateSlide(index, direction);
                resetAutoplay();
            };

            window.nextSlide = function() {
                const next = (currentSlide + 1) % totalSlides;
                updateSlide(next, 'next');
                resetAutoplay();
            };

            window.prevSlide = function() {
                const prev = (currentSlide - 1 + totalSlides) % totalSlides;
                updateSlide(prev, 'prev');
                resetAutoplay();
            };

            function startAutoplay() {
                if (totalSlides <= 1) return;
                autoplayTimer = setInterval(() => {
                    const next = (currentSlide + 1) % totalSlides;
                    updateSlide(next, 'next');
                }, 7000);
            }

            function resetAutoplay() {
                clearInterval(autoplayTimer);
                startAutoplay();
            }

            if (totalSlides > 1) {
                startAutoplay();
                const carouselEl = document.getElementById('motivationCarousel');
                if (carouselEl) {
                    carouselEl.addEventListener('mouseenter', () => clearInterval(autoplayTimer));
                    carouselEl.addEventListener('mouseleave', () => startAutoplay());
                }
            }
        });
    </script>
@endsection