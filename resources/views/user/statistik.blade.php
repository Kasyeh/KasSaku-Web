@extends('template.masteru')

@section('page_title', 'Dashboard Analytics 📊')
@section('page_subtitle', 'Pantau kesehatan finansial Anda dalam satu layar')

@section('content')
    <style>
        @keyframes expense-alert-blink {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.35;
            }
        }

        .expense-alert-blink {
            animation: expense-alert-blink 0.9s ease-in-out infinite;
        }
    </style>

    @php
        $expenseCardClass = $isExpenseHigherThanIncome
            ? 'card-premium hover-card rounded-[40px] p-8 flex flex-col justify-between ring-2 ring-rose-300/70 shadow-xl shadow-rose-500/10'
            : 'card-premium hover-card rounded-[40px] p-8 flex flex-col justify-between';
        $expenseTextClass = $isExpenseHigherThanIncome
            ? 'text-2xl font-black text-rose-600 expense-alert-blink mt-2'
            : 'text-2xl font-black ' . ($isOverBudget ? 'text-rose-600' : 'text-slate-800 dark:text-white') . ' mt-2';
    @endphp

    {{-- Initial Page Skeleton (Professional Transition) --}}
    <div id="page-skeleton">
        @include('user.skeletons.statistik')
    </div>

    <div id="main-content" class="max-w-6xl mx-auto space-y-10 hidden">
        <div class="flex justify-end">
            <button
                type="button"
                data-guide-open="statistik"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-primary-500/10 hover:bg-primary-500 text-primary-600 hover:text-white text-[10px] font-black uppercase tracking-[0.18em] transition-all active:scale-95 border border-primary-500/20"
            >
                <span class="material-icons-round text-sm">help_outline</span>
                Panduan
            </button>
        </div>

        @include('user.partials.guide-card', [
            'guideId' => 'statistik',
            'title' => 'Memahami Dashboard Keuangan Anda',
            'description' => 'Halaman ini merangkum kondisi keuangan aktif Anda agar lebih mudah dibaca sebelum mencatat transaksi baru.',
            'items' => [
                'Uang kamu saat ini adalah hasil dari semua uang masuk dikurangi semua uang keluar yang masih tercatat.',
                'Uang masuk dan keluar bulan ini hanya menghitung catatan pada bulan berjalan.',
                'Batas belanja per jenis membantu membatasi belanja agar tidak melewati batas.',
                'Setoran impian dicatat sebagai pengeluaran khusus agar progres impian dan saldo tetap konsisten.',
            ],
        ])

        {{-- Top Section: Balances & Summaries (Premium Redesign) --}}
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-stretch">
            {{-- Main Balance Card (Emerald Premium) --}}
            <div class="lg:col-span-8 group relative overflow-hidden rounded-[48px] p-10 shadow-2xl transition-all duration-500 hover:shadow-primary-500/20">
                {{-- Background Layers --}}
                <div class="absolute inset-0 bg-gradient-to-br from-emerald-600 via-emerald-500 to-primary-500"></div>
                <div class="absolute -top-32 -right-32 w-96 h-96 bg-white/20 blur-[100px] rounded-full mix-blend-overlay animate-pulse"></div>
                <div class="absolute -bottom-32 -left-32 w-80 h-80 bg-primary-300/20 blur-[80px] rounded-full mix-blend-overlay"></div>
                
                {{-- Decorative Card Chip SVG --}}
                <div class="absolute top-10 right-10 opacity-20 group-hover:opacity-40 transition-opacity duration-700 pointer-events-none">
                    <svg width="80" height="80" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="text-white">
                        <rect x="2" y="5" width="20" height="14" rx="2" stroke="currentColor" stroke-width="1"/>
                        <path d="M2 10H22M7 15H8M11 15H13" stroke="currentColor" stroke-width="1" stroke-linecap="round"/>
                    </svg>
                </div>

                <div class="relative z-10 h-full flex flex-col justify-between">
                    <div>
                        <div class="flex items-center justify-between gap-4 mb-6">
                            <div class="flex items-center gap-3">
                                <div class="w-1.5 h-6 bg-white/40 rounded-full"></div>
                                <h3 class="text-[10px] font-black uppercase tracking-[0.3em] text-white/80">Total Saldo Aktif</h3>
                            </div>
                            <button
                                type="button"
                                id="balance-visibility-toggle"
                                class="w-12 h-12 flex items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-white/90 hover:text-white hover:bg-white/20 transition-all active:scale-95"
                                aria-label="Sembunyikan saldo"
                                aria-pressed="false"
                            >
                                <span id="balance-visibility-icon" class="material-icons-round text-xl">visibility</span>
                            </button>
                        </div>
                        <div class="flex items-baseline gap-3">
                            <span class="text-2xl text-white/50 font-bold tracking-tight">IDR</span>
                            <h2 id="rt-balance-main" class="text-6xl md:text-7xl font-black tracking-tighter text-white rt-balance drop-shadow-sm">
                                {{ number_format($statistik->saldo ?? 0, 0, ',', '.') }}
                            </h2>
                        </div>
                    </div>

                    <div class="flex items-center gap-4 mt-12">
                        <button onclick="showTransactionModal('pemasukan')"
                            class="flex-1 py-4.5 bg-white/10 hover:bg-white text-white hover:text-emerald-600 backdrop-blur-xl rounded-[24px] border border-white/20 font-black text-xs uppercase tracking-widest transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 group/btn">
                            <span class="material-icons-round text-lg group-hover/btn:rotate-12 transition-transform">add_circle</span>
                            Uang Masuk
                        </button>
                        <button onclick="showTransactionModal('pengeluaran')"
                            class="flex-1 py-4.5 bg-white/10 hover:bg-white text-white hover:text-rose-500 backdrop-blur-xl rounded-[24px] border border-white/20 font-black text-xs uppercase tracking-widest transition-all hover:-translate-y-1 active:scale-95 flex items-center justify-center gap-2 group/btn">
                            <span class="material-icons-round text-lg group-hover/btn:-rotate-12 transition-transform">remove_circle</span>
                            Uang Keluar
                        </button>
                    </div>
                </div>
            </div>

            {{-- Summary Sidebar (Dark Premium) --}}
            <div class="lg:col-span-4 flex flex-col gap-6">
                {{-- Monthly Income Card --}}
                <div class="flex-1 relative overflow-hidden rounded-[40px] p-8 bg-white/70 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/50 dark:border-white/5 shadow-2xl group transition-all duration-500 hover:border-emerald-500/20">
                    <div class="absolute -top-12 -right-12 w-40 h-40 bg-emerald-500/10 blur-[60px] rounded-full transition-all duration-700 group-hover:scale-125"></div>
                    
                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-2 mb-4">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pemasukan Bulan Ini</h4>
                            </div>
                            <div class="flex items-baseline gap-1.5">
                                <span class="text-xs font-bold text-slate-500">Rp</span>
                                <p class="text-3xl font-black text-emerald-400 tracking-tight">
                                    <span id="rt-monthly-pemasukan-value" class="rt-monthly-pemasukan">{{ number_format($monthlyPemasukan ?? 0, 0, ',', '.') }}</span>
                                </p>
                            </div>
                        </div>
                        <div class="mt-6">
                            <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shadow-inner">
                                <div class="bg-gradient-to-r from-emerald-500 to-primary-400 h-full w-[65%] rounded-full shadow-[0_0_15px_rgba(16,185,129,0.5)] transition-all duration-1000"></div>
                            </div>
                            <p class="text-[8px] font-black text-slate-500 uppercase tracking-widest mt-3 text-right">Statistik Berjalan</p>
                        </div>
                    </div>
                </div>

                {{-- Monthly Expense Card --}}
                <div id="rt-monthly-pengeluaran-card" class="flex-1 relative overflow-hidden rounded-[40px] p-8 bg-white/70 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/50 dark:border-white/5 shadow-2xl group transition-all duration-500 hover:border-rose-500/20">
                    <div class="absolute -top-12 -right-12 w-40 h-40 bg-rose-500/10 blur-[60px] rounded-full transition-all duration-700 group-hover:scale-125"></div>
                    
                    <div class="relative z-10 h-full flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div>
                                <div class="flex items-center gap-2 mb-4">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 {{ $isOverBudget ? 'animate-ping' : 'animate-pulse' }}"></span>
                                    <h4 class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Pengeluaran Bulan Ini</h4>
                                </div>
                                <div class="flex items-baseline gap-1.5">
                                    <span class="text-xs font-bold text-slate-500">Rp</span>
                                    <p id="rt-monthly-pengeluaran-text" class="text-3xl font-black {{ $isExpenseHigherThanIncome || $isOverBudget ? 'text-rose-500' : 'text-slate-900 dark:text-slate-100' }} tracking-tight">
                                        <span id="rt-monthly-pengeluaran-value" class="rt-monthly-pengeluaran">{{ number_format($monthlyPengeluaran ?? 0, 0, ',', '.') }}</span>
                                    </p>
                                </div>
                            </div>
                            <div id="rt-over-budget-badge-container" class="flex flex-col items-end gap-1.5">
                                @if($isOverBudget)
                                    <span class="px-2.5 py-1 bg-red-500/10 border border-red-500/20 text-red-500 text-[8px] font-black rounded-lg animate-pulse tracking-widest uppercase">Over Budget</span>
                                @endif
                                @if($isExpenseHigherThanIncome)
                                    <span class="px-2.5 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-500 text-[8px] font-black rounded-lg tracking-widest uppercase">Waspada</span>
                                @endif
                            </div>
                        </div>

                        <div id="rt-target-progress-section" class="mt-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Target: Rp <span id="rt-target-pengeluaran-main" class="rt-target-pengeluaran">{{ $targetPengeluaran !== null ? number_format($targetPengeluaran, 0, ',', '.') : '0' }}</span></span>
                                @php $perc = $targetPengeluaran > 0 ? min(100, ($monthlyPengeluaran / $targetPengeluaran) * 100) : ($targetPengeluaran === null ? 0 : 100); @endphp
                                <span class="text-[9px] font-black text-slate-400">{{ round($perc) }}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shadow-inner">
                                <div class="{{ $isOverBudget ? 'bg-rose-500' : 'bg-primary-500' }} h-full transition-all duration-1000 shadow-[0_0_15px_rgba(244,63,94,0.3)]"
                                    style="width: {{ $perc }}%"></div>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <button onclick="setTargetBudget()"
                                    class="text-[9px] font-black text-emerald-500 hover:text-emerald-400 flex items-center gap-1.5 transition-all uppercase tracking-wider">
                                    <span class="material-icons-round text-xs">tune</span> {{ $targetPengeluaran !== null ? 'Ubah Limit' : 'Atur Batas Belanja' }}
                                </button>
                                @if($isOverBudget)
                                    <span class="material-icons-round text-rose-500 text-sm animate-bounce">warning</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>



    {{-- Insight Section (Android Harmony) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 animate-slide-up">
        {{-- Avg Savings --}}
        <div class="card-premium hover-card p-6 rounded-[32px] group text-center lg:text-left">
            <div class="w-10 h-10 rounded-2xl bg-primary-100 dark:bg-primary-900/30 flex items-center justify-center text-primary-600 mb-4 mx-auto lg:mx-0 group-hover:rotate-12 transition-transform">
                <span class="material-icons-round">paid</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Rata Tabungan</p>
            <p class="text-sm font-black text-slate-800 dark:text-white mt-1">Rp <span id="rt-avg-savings-value">{{ number_format($avgSavings, 0, ',', '.') }}</span></p>
        </div>
        
        {{-- Tren --}}
        <div class="card-premium hover-card p-6 rounded-[32px] group text-center lg:text-left">
            <div id="rt-trend-icon-wrapper" class="w-10 h-10 rounded-2xl {{ $trend == 'Meningkat' ? 'bg-green-100 dark:bg-green-900/30 text-green-600' : ($trend == 'Menurun' ? 'bg-red-100 dark:bg-red-900/30 text-red-600' : 'bg-slate-100 dark:bg-slate-800 text-slate-400') }} flex items-center justify-center mb-4 mx-auto lg:mx-0 group-hover:scale-110 transition-transform">
                <span id="rt-trend-icon" class="material-icons-round">{{ $trend == 'Meningkat' ? 'trending_up' : ($trend == 'Menurun' ? 'trending_down' : 'trending_flat') }}</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tren Finansial</p>
            <p id="rt-trend-value" class="text-sm font-black text-slate-800 dark:text-white mt-1">{{ $trend }}</p>
        </div>

        {{-- Best Month --}}
        <div class="card-premium hover-card p-6 rounded-[32px] group text-center lg:text-left">
            <div class="w-10 h-10 rounded-2xl bg-secondary-100 dark:bg-secondary-900/30 flex items-center justify-center text-secondary-600 mb-4 mx-auto lg:mx-0">
                <span class="material-icons-round">workspace_premium</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bulan Terhemat</p>
            <p id="rt-most-productive-month" class="text-sm font-black text-slate-800 dark:text-white mt-1">{{ $mostProductiveMonth ?? '-' }}</p>
        </div>

        {{-- Peak Spending --}}
        <div class="card-premium hover-card p-6 rounded-[32px] group text-center lg:text-left">
            <div class="w-10 h-10 rounded-2xl bg-amber-100 dark:bg-amber-900/30 flex items-center justify-center text-amber-600 mb-4 mx-auto lg:mx-0">
                <span class="material-icons-round">warning_amber</span>
            </div>
            <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Bulan Terboros</p>
            <p id="rt-most-wasteful-month" class="text-sm font-black text-slate-800 dark:text-white mt-1">{{ $mostWastefulMonth ?? '-' }}</p>
        </div>
    {{-- Insight Cards (existing code above) --}}
    </div>

    {{-- 💰 Budget per Kategori Section --}}
    <div id="budget-section-container">
        @include('user.partials.statistik.budget-section')
    </div>
    <div id="dream-forecast-container">
        @include('user.partials.statistik.dream-forecast')
    </div>

        {{-- Chart & Activity --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            {{-- Main Chart --}}
            <div
                class="lg:col-span-2 card-premium hover-card rounded-[40px] p-10">
                <div class="flex flex-col gap-4 mb-8">
                    <div class="flex items-center justify-between">
                        <h3 class="font-bold text-slate-800 dark:text-white flex items-center gap-2">
                        <span class="material-icons-round text-primary-500">bar_chart</span> Grafik Arus Kas
                        </h3>
                    </div>
                    <div class="flex flex-wrap gap-2" id="cashflow-period-switcher">
                        <button type="button" data-cashflow-period="7d" class="cashflow-period-btn px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300">7 Hari</button>
                        <button type="button" data-cashflow-period="30d" class="cashflow-period-btn px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-primary-500 text-white">30 Hari</button>
                        <button type="button" data-cashflow-period="3m" class="cashflow-period-btn px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300">3 Bulan</button>
                        <button type="button" data-cashflow-period="12m" class="cashflow-period-btn px-3 py-1.5 rounded-full text-[10px] font-black uppercase tracking-widest bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300">12 Bulan</button>
                    </div>
                </div>
                <div class="h-[260px]">
                    <canvas id="financeTrendChart"></canvas>
                </div>
                <div class="h-[170px] mt-6">
                    <canvas id="financeNetChart"></canvas>
                </div>
                <div id="cashflowInsight" class="mt-6 p-4 rounded-2xl bg-slate-50 dark:bg-white/5 text-xs font-semibold text-slate-600 dark:text-slate-300"></div>
                <div class="mt-3 text-[10px] font-bold text-slate-400 uppercase tracking-wider">
                    Garis tren menampilkan pemasukan/pengeluaran/net. Bar bawah menampilkan net arus kas per periode.
                </div>
            </div>

            {{-- Recent Activities --}}
            <div id="recent-activities-container">
                @include('user.partials.statistik.recent-activities')
            </div>
        </div>

            <div id="performance-summary-container">
                @include('user.partials.statistik.performance-summary')
            </div>

    </div>
@endsection

@section('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            window.kasSakuGuide?.initGuide('statistik');

            // Handle actions from Beranda
            const urlParams = new URLSearchParams(window.location.search);
            const actionParam = urlParams.get('action');
            if (actionParam === 'pemasukan' || actionParam === 'pengeluaran') {
                setTimeout(() => {
                    if (typeof window.showTransactionModal === 'function') {
                        window.showTransactionModal(actionParam);
                    }
                }, 100);
            }

            const csrfToken = '{{ csrf_token() }}';
            const statistikSnapshotUrl = '{{ route("user.statistik.snapshot") }}';
            const balanceVisibilityStorageKey = 'kassaku.balanceVisibility.statistik';
            let kategoriList = {!! json_encode($kategoriList ?? []) !!};
            let kategoriCepatPemasukan = {!! json_encode($kategoriCepatPemasukan ?? []) !!};
            let kategoriCepatPengeluaran = {!! json_encode($kategoriCepatPengeluaran ?? []) !!};
            let budgetKategoriList = {!! json_encode($budgetKategoriList ?? []) !!};
            let cashflowSeries = {!! json_encode($cashflowSeries ?? []) !!};
            let currentCashflowPeriod = '30d';
            let suppressRealtimeUntil = 0;
            let snapshotSyncTimer = null;
            let hasReceivedInitialRealtimeBalance = false;
            let isBalanceVisible = true;
            let currentBalanceValue = Number(@json((float) ($statistik->saldo ?? 0)));
            const nominalCepat = [10000, 20000, 50000, 100000, 200000, 500000];

            const sanitizeNominal = (value) => value.replace(/\D/g, "");
            const formatNominal = (value) => value ? new Intl.NumberFormat("id-ID").format(value) : "";
            const formatStatValue = (value) => new Intl.NumberFormat("id-ID").format(Number(value || 0));
            const toTitle = (value) => value ? value.charAt(0).toUpperCase() + value.slice(1) : '';
            const escapeHtml = (value) => String(value)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
            const uniqueKategoriCaseInsensitive = (...items) => {
                const seen = new Set();

                return items
                    .flat()
                    .map(v => String(v || '').trim())
                    .filter(Boolean)
                    .filter((value) => {
                        const key = value.toLocaleLowerCase('id-ID');

                        if (seen.has(key)) {
                            return false;
                        }

                        seen.add(key);
                        return true;
                    });
            };
            const renderKategoriDatalistOptions = (items) => items
                .map(k => `<option value="${escapeHtml(toTitle(k))}">`)
                .join('');
            const trendVisualMap = {
                Meningkat: {
                    wrapper: 'bg-green-100 dark:bg-green-900/30 text-green-600',
                    icon: 'trending_up',
                },
                Menurun: {
                    wrapper: 'bg-red-100 dark:bg-red-900/30 text-red-600',
                    icon: 'trending_down',
                },
                Stabil: {
                    wrapper: 'bg-slate-100 dark:bg-slate-800 text-slate-400',
                    icon: 'trending_flat',
                },
            };

            const setInnerHtml = (id, html) => {
                const el = document.getElementById(id);
                if (el) {
                    el.innerHTML = html || '';
                }
            };

            const getStoredBalanceVisibility = () => {
                try {
                    const storedValue = window.localStorage.getItem(balanceVisibilityStorageKey);
                    return storedValue === null ? true : storedValue === 'true';
                } catch (error) {
                    console.warn('Gagal membaca preferensi visibilitas saldo:', error);
                    return true;
                }
            };

            const persistBalanceVisibility = (value) => {
                try {
                    window.localStorage.setItem(balanceVisibilityStorageKey, String(value));
                } catch (error) {
                    console.warn('Gagal menyimpan preferensi visibilitas saldo:', error);
                }
            };

            const getMaskedBalanceText = () => '•••••••';

            const renderMainBalance = (value = currentBalanceValue) => {
                currentBalanceValue = Number(value || 0);
                const balanceEl = document.getElementById('rt-balance-main');
                if (balanceEl) {
                    balanceEl.innerText = isBalanceVisible
                        ? formatStatValue(currentBalanceValue)
                        : getMaskedBalanceText();
                }
            };

            const updateBalanceToggleUi = () => {
                const toggleButton = document.getElementById('balance-visibility-toggle');
                const iconEl = document.getElementById('balance-visibility-icon');

                if (toggleButton) {
                    toggleButton.setAttribute('aria-label', isBalanceVisible ? 'Sembunyikan saldo' : 'Tampilkan saldo');
                    toggleButton.setAttribute('aria-pressed', String(!isBalanceVisible));
                }

                if (iconEl) {
                    iconEl.innerText = isBalanceVisible ? 'visibility' : 'visibility_off';
                }
            };

            const parseJsonResponse = async (response, fallbackMessage) => {
                const contentType = response.headers.get('content-type') || '';
                let payload = null;

                if (contentType.includes('application/json')) {
                    payload = await response.json();
                } else {
                    const text = await response.text();
                    throw new Error(text ? text.slice(0, 200) : fallbackMessage);
                }

                if (!response.ok || payload?.success === false) {
                    const validationErrors = payload?.errors && typeof payload.errors === 'object'
                        ? Object.values(payload.errors).flat().filter(Boolean)
                        : [];
                    throw new Error(validationErrors[0] || payload?.message || fallbackMessage);
                }

                return payload;
            };

            const applySummaryToDom = (summary) => {
                if (!summary || typeof summary !== 'object') {
                    throw new Error('Snapshot statistik tidak lengkap.');
                }

                renderMainBalance(summary.saldo);
                document.querySelectorAll('.rt-monthly-pemasukan').forEach((el) => {
                    el.innerText = formatStatValue(summary.monthly_pemasukan);
                });
                document.querySelectorAll('.rt-monthly-pengeluaran').forEach((el) => {
                    el.innerText = formatStatValue(summary.monthly_pengeluaran);
                });
                document.querySelectorAll('.rt-target-pengeluaran').forEach((el) => {
                    el.innerText = summary.target_pengeluaran !== null && summary.target_pengeluaran !== undefined
                        ? formatStatValue(summary.target_pengeluaran)
                        : '0';
                });

                const avgSavingsEl = document.getElementById('rt-avg-savings-value');
                if (avgSavingsEl) {
                    avgSavingsEl.innerText = formatStatValue(summary.avg_savings);
                }

                const productiveMonthEl = document.getElementById('rt-most-productive-month');
                if (productiveMonthEl) {
                    productiveMonthEl.innerText = summary.most_productive_month || '-';
                }

                const wastefulMonthEl = document.getElementById('rt-most-wasteful-month');
                if (wastefulMonthEl) {
                    wastefulMonthEl.innerText = summary.most_wasteful_month || '-';
                }

                const isExpenseHigherThanIncome = Boolean(summary.is_expense_higher_than_income);
                const expenseCardEl = document.getElementById('rt-monthly-pengeluaran-card');
                if (expenseCardEl) {
                    expenseCardEl.className = isExpenseHigherThanIncome
                        ? 'flex-1 relative overflow-hidden rounded-[40px] p-8 bg-white/70 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/50 dark:border-white/5 ring-2 ring-rose-300/40 shadow-xl shadow-rose-500/5 group transition-all duration-500 hover:border-rose-500/20'
                        : 'flex-1 relative overflow-hidden rounded-[40px] p-8 bg-white/70 dark:bg-slate-900/90 backdrop-blur-xl border border-slate-200/50 dark:border-white/5 shadow-2xl group transition-all duration-500 hover:border-rose-500/20';
                }

                const expenseTextEl = document.getElementById('rt-monthly-pengeluaran-text');
                if (expenseTextEl) {
                    expenseTextEl.className = isExpenseHigherThanIncome
                        ? 'text-2xl font-black text-rose-600 expense-alert-blink mt-2'
                        : `text-2xl font-black ${summary.is_over_budget ? 'text-rose-600' : 'text-slate-900 dark:text-white'} mt-2`;
                }

                const badgeContainer = document.getElementById('rt-over-budget-badge-container');
                if (badgeContainer) {
                    const badges = [];

                    if (summary.is_over_budget) {
                        badges.push('<span class="px-2.5 py-1 bg-red-500/10 border border-red-500/20 text-red-500 text-[8px] font-black rounded-lg animate-pulse tracking-widest uppercase">Over Budget</span>');
                    }

                    if (isExpenseHigherThanIncome) {
                        badges.push('<span class="px-2.5 py-1 bg-rose-500/10 border border-rose-500/20 text-rose-500 text-[8px] font-black rounded-lg tracking-widest uppercase">Waspada</span>');
                    }

                    badgeContainer.className = 'flex flex-col items-end gap-1.5';
                    badgeContainer.innerHTML = badges.join('');
                }

                const trendVisual = trendVisualMap[summary.trend] || trendVisualMap.Stabil;
                const trendWrapperEl = document.getElementById('rt-trend-icon-wrapper');
                if (trendWrapperEl) {
                    trendWrapperEl.className = `w-10 h-10 rounded-2xl ${trendVisual.wrapper} flex items-center justify-center mb-4 mx-auto lg:mx-0 group-hover:scale-110 transition-transform`;
                }

                const trendIconEl = document.getElementById('rt-trend-icon');
                if (trendIconEl) {
                    trendIconEl.innerText = summary.trend_icon || trendVisual.icon;
                }

                const trendValueEl = document.getElementById('rt-trend-value');
                if (trendValueEl) {
                    trendValueEl.innerText = summary.trend || 'Stabil';
                }

                const targetProgressSection = document.getElementById('rt-target-progress-section');
                if (targetProgressSection) {
                    if (summary.target_pengeluaran !== null && summary.target_pengeluaran !== undefined) {
                        const progress = Number(summary.target_progress_percent ?? 0);
                        targetProgressSection.innerHTML = `
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-[9px] font-black text-slate-500 uppercase tracking-widest">Target: Rp <span id="rt-target-pengeluaran-main" class="rt-target-pengeluaran">${formatStatValue(summary.target_pengeluaran)}</span></span>
                                <span class="text-[9px] font-black text-slate-400">${Math.round(progress)}%</span>
                            </div>
                            <div class="w-full h-1.5 bg-slate-100 dark:bg-slate-800 rounded-full overflow-hidden shadow-inner">
                                <div class="${summary.is_over_budget ? 'bg-rose-500' : 'bg-primary-500'} h-full transition-all duration-1000 shadow-[0_0_15px_rgba(244,63,94,0.3)]" style="width: ${progress}%"></div>
                            </div>
                            <div class="mt-4 flex justify-between items-center">
                                <button onclick="setTargetBudget()"
                                    class="text-[9px] font-black text-emerald-500 hover:text-emerald-400 flex items-center gap-1.5 transition-all uppercase tracking-wider">
                                    <span class="material-icons-round text-xs">tune</span> Ubah Limit
                                </button>
                                ${summary.is_over_budget ? '<span class="material-icons-round text-rose-500 text-sm animate-bounce">warning</span>' : ''}
                            </div>
                        `;
                    } else {
                        targetProgressSection.innerHTML = `
                            <button onclick="setTargetBudget()"
                                class="mt-4 text-[10px] font-black text-emerald-500 hover:text-emerald-400 flex items-center gap-1.5 transition-all uppercase tracking-wider">
                                <span class="material-icons-round text-sm">tune</span> Atur Batas Belanja
                            </button>
                        `;
                    }
                }
            };

            const applyAuxiliarySnapshotState = (snapshotData) => {
                if (Array.isArray(snapshotData.kategori_list)) {
                    kategoriList = snapshotData.kategori_list;
                }
                if (Array.isArray(snapshotData.kategori_cepat_pemasukan)) {
                    kategoriCepatPemasukan = snapshotData.kategori_cepat_pemasukan;
                }
                if (Array.isArray(snapshotData.kategori_cepat_pengeluaran)) {
                    kategoriCepatPengeluaran = snapshotData.kategori_cepat_pengeluaran;
                }
                if (Array.isArray(snapshotData.budget_kategori_list)) {
                    budgetKategoriList = snapshotData.budget_kategori_list;
                }
                if (snapshotData.cashflow_series && typeof snapshotData.cashflow_series === 'object') {
                    cashflowSeries = snapshotData.cashflow_series;
                }
            };

            window.applyStatistikSnapshot = function (snapshotData) {
                if (!snapshotData || typeof snapshotData !== 'object') {
                    throw new Error('Snapshot statistik tidak tersedia.');
                }

                applySummaryToDom(snapshotData.summary);
                applyAuxiliarySnapshotState(snapshotData);

                const fragments = snapshotData.fragments || {};
                setInnerHtml('budget-section-container', fragments.budget_section_html || '');
                setInnerHtml('dream-forecast-container', fragments.dream_forecast_html || '');
                setInnerHtml('recent-activities-container', fragments.recent_activities_html || '');
                setInnerHtml('performance-summary-container', fragments.performance_summary_html || '');

                renderCashflowCharts(currentCashflowPeriod);
            };

            window.fetchStatistikSnapshot = async function ({ silent = false } = {}) {
                try {
                    const response = await fetch(statistikSnapshotUrl, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                    });
                    const payload = await parseJsonResponse(response, 'Gagal memuat statistik terbaru.');
                    window.applyStatistikSnapshot(payload.data);
                    return payload.data;
                } catch (error) {
                    if (!silent) {
                        window.showAlert('error', 'Gagal!', error.message || 'Gagal memuat statistik terbaru.');
                    }
                    throw error;
                }
            };

            window.syncStatistikSnapshotDebounced = function () {
                if (Date.now() < suppressRealtimeUntil) {
                    return;
                }

                clearTimeout(snapshotSyncTimer);
                snapshotSyncTimer = setTimeout(() => {
                    window.fetchStatistikSnapshot({ silent: true }).catch((error) => {
                        console.error('Snapshot statistik gagal disinkronkan:', error);
                    });
                }, 250);
            };

            const syncMutationPayload = async (payload) => {
                let synchronized = false;

                if (payload?.data) {
                    try {
                        window.applyStatistikSnapshot(payload.data);
                        synchronized = true;
                    } catch (error) {
                        console.error('Gagal menerapkan snapshot langsung:', error);
                    }
                }

                if (!synchronized) {
                    try {
                        await window.fetchStatistikSnapshot({ silent: true });
                        synchronized = true;
                    } catch (error) {
                        console.error('Fallback snapshot gagal:', error);
                    }
                }

                if (!synchronized) {
                    throw new Error('Perubahan berhasil disimpan, tetapi dashboard gagal disinkronkan. Muat ulang halaman.');
                }

                suppressRealtimeUntil = Date.now() + 1500;
            };

            isBalanceVisible = getStoredBalanceVisibility();
            updateBalanceToggleUi();
            renderMainBalance(currentBalanceValue);

            document.getElementById('balance-visibility-toggle')?.addEventListener('click', () => {
                isBalanceVisible = !isBalanceVisible;
                persistBalanceVisibility(isBalanceVisible);
                updateBalanceToggleUi();
                renderMainBalance(currentBalanceValue);
            });

            window.handleRealtimeBalanceUpdate = (saldo) => {
                currentBalanceValue = Number(saldo || 0);
                renderMainBalance(currentBalanceValue);
                return true;
            };

            // Quotes Logic
            let currentQuote = 0;
            const quotes = document.querySelectorAll('.quote-item');
            if (quotes.length > 1) {
                setInterval(() => {
                    quotes[currentQuote].classList.add('hidden');
                    currentQuote = (currentQuote + 1) % quotes.length;
                    quotes[currentQuote].classList.remove('hidden');
                }, 6000);
            }

            // Action Handlers
            window.showTransactionModal = function (type) {
                const kategoriDropdown = type === 'pengeluaran'
                    ? uniqueKategoriCaseInsensitive(
                        budgetKategoriList,
                        budgetKategoriList.length ? [] : kategoriCepatPengeluaran,
                        budgetKategoriList.length ? [] : kategoriList
                    )
                    : uniqueKategoriCaseInsensitive(kategoriCepatPemasukan);
                const kategoriCepat = kategoriDropdown.slice(0, 8);
                const datalistOptions = renderKategoriDatalistOptions(kategoriDropdown);
                const emptyKategoriMessage = type === 'pengeluaran'
                    ? 'Belum ada batas belanja per jenis.'
                    : 'Belum ada histori kategori.';
                const quickKategoriButtons = kategoriCepat.length
                    ? kategoriCepat.map(k => `
                        <button type="button" data-quick-kategori="${encodeURIComponent(k)}" class="px-3 py-1.5 rounded-full bg-slate-200/70 dark:bg-slate-700/70 text-[10px] font-black text-slate-600 dark:text-slate-200 hover:bg-primary-500 hover:text-white transition-all">
                            ${escapeHtml(toTitle(k))}
                        </button>
                    `).join('')
                    : `<span class="text-[10px] text-slate-400">${emptyKategoriMessage}</span>`;
                const quickNominalButtons = nominalCepat.map(n => `
                    <button type="button" data-quick-nominal="${n}" class="px-3 py-1.5 rounded-full bg-slate-200/70 dark:bg-slate-700/70 text-[10px] font-black text-slate-600 dark:text-slate-200 hover:bg-primary-500 hover:text-white transition-all">
                        Rp ${new Intl.NumberFormat("id-ID").format(n)}
                    </button>
                `).join('');

                Swal.fire({
                    title: 'Tambah ' + (type === 'pemasukan' ? 'Uang Masuk' : 'Uang Keluar'),
                    html: `
                            <div class="space-y-6 pt-4 text-left">
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Nominal (Rp)</label>
                                    <div class="relative group">
                                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-primary-500 font-black text-xl">Rp</span>
                                        <input id="swal-nominal" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-3xl !py-6 !pl-16 !pr-6 !font-black !text-3xl !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all tracking-tighter" type="text" placeholder="0">
                                    </div>
                                    <div class="flex flex-wrap gap-2 ml-2" id="swal-nominal-cepat">${quickNominalButtons}</div>
                                </div>
                                
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div class="space-y-4">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Jenis</label>
                                        <div class="relative group">
                                            <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">category</span>
                                            <input id="swal-kategori" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-5 !pl-14 !pr-6 !font-bold !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all text-sm" list="transactionKategoriOptions" placeholder="${type === 'pemasukan' ? 'Misal: Gaji' : (budgetKategoriList.length ? 'Pilih dari batas belanja...' : 'Pilih...')}">
                                        </div>
                                        <datalist id="transactionKategoriOptions">${datalistOptions}</datalist>
                                        <div class="flex flex-wrap gap-2 mt-2 ml-2" id="swal-kategori-cepat">${quickKategoriButtons}</div>
                                    </div>
                                    
                                    <div class="space-y-4">
                                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Tanggal</label>
                                        <div class="relative group">
                                            <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">calendar_month</span>
                                            <input id="swal-tanggal" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-5 !pl-14 !pr-6 !font-bold !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all text-sm" type="date" value="{{ date('Y-m-d') }}">
                                        </div>
                                    </div>
                                </div>

                                <div class="space-y-4 pt-2">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Keterangan (Opsional)</label>
                                    <div class="relative group">
                                        <span class="material-icons-round absolute left-5 top-5 text-slate-300 group-focus-within:text-primary-500 transition-colors">notes</span>
                                        <textarea id="swal-keterangan" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-3xl !py-4 !pl-14 !pr-6 !font-bold !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all !h-28 text-sm resize-none" placeholder="Tambahkan catatan..."></textarea>
                                    </div>
                                </div>
                            </div>
                        `,
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#94a3b8',
                    background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                    didOpen: () => {
                        const nominalInput = document.getElementById('swal-nominal');
                        const kategoriInput = document.getElementById('swal-kategori');

                        nominalInput.addEventListener('input', (e) => {
                            const v = sanitizeNominal(e.target.value);
                            e.target.value = formatNominal(v);
                        });

                        document.querySelectorAll('[data-quick-nominal]').forEach(btn => {
                            btn.addEventListener('click', () => {
                                nominalInput.value = formatNominal(btn.dataset.quickNominal);
                                nominalInput.focus();
                            });
                        });

                        document.querySelectorAll('[data-quick-kategori]').forEach(btn => {
                            btn.addEventListener('click', () => {
                                kategoriInput.value = toTitle(decodeURIComponent(btn.dataset.quickKategori || ''));
                                kategoriInput.focus();
                            });
                        });
                    },
                    preConfirm: () => {
                        const nominal = sanitizeNominal(document.getElementById('swal-nominal').value);
                        const kategori = document.getElementById('swal-kategori').value.trim();
                        const tanggalInput = document.getElementById('swal-tanggal').value;
                        const keterangan = document.getElementById('swal-keterangan').value.trim();
                        
                        if (!nominal || !kategori || !tanggalInput) return Swal.showValidationMessage('Isi data wajib dengan lengkap');
                        
                        // Append current time to date to avoid 00:00:00 issue
                        const now = new Date();
                        const timeStr = now.getHours().toString().padStart(2, '0') + ':' + 
                                      now.getMinutes().toString().padStart(2, '0') + ':' + 
                                      now.getSeconds().toString().padStart(2, '0');
                        const tanggal = `${tanggalInput} ${timeStr}`;
                                      
                        return { nominal, kategori, tanggal, keterangan };
                    }
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        const url = type === 'pemasukan' ? '{{ url("user/simpanPemasukan") }}' : '{{ url("user/simpanPengeluaran") }}';
                        try {
                            const response = await fetch(url, {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(result.value)
                            });
                            const data = await parseJsonResponse(response, 'Terjadi kesalahan saat menyimpan data.');
                            await syncMutationPayload(data);
                            window.showAlert('success', 'Berhasil!', data.message || 'Transaksi berhasil disimpan.');
                        } catch (error) {
                            window.showAlert('error', 'Gagal!', error.message || 'Terjadi kesalahan saat menyimpan data.');
                        }
                    }
                });
            };

            window.setTargetBudget = function () {
                Swal.fire({
                    title: 'Set Limit Budget',
                    html: `
                    <div class="space-y-4 pt-4 text-left">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Target Bulanan (Rp)</label>
                        <div class="relative group">
                            <span class="absolute left-6 top-1/2 -translate-y-1/2 text-primary-500 font-black text-xl">Rp</span>
                            <input id="swal-target" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-3xl !py-6 !pl-16 !pr-8 !font-black !text-3xl !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all tracking-tighter" type="text" value="{{ $targetPengeluaran ? number_format($targetPengeluaran, 0, ',', '.') : '' }}">
                        </div>
                        <p class="text-[9px] text-slate-400 ml-2 italic leading-relaxed mt-2">
                            Beritahu kami batas pengeluaran bulanan Anda untuk membantu menjaga kesehatan finansial.
                        </p>
                    </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Simpan Target',
                    background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                    didOpen: () => {
                        const input = document.getElementById('swal-target');
                        input.addEventListener('input', (e) => {
                            let v = e.target.value.replace(/\D/g, "");
                            e.target.value = v ? new Intl.NumberFormat("id-ID").format(v) : "";
                        });
                    },
                    preConfirm: () => {
                        const targetValue = document.getElementById('swal-target').value;
                        if (targetValue === "") return Swal.showValidationMessage('Isi target dengan nominal yang benar');
                        const target = targetValue.replace(/\./g, '');
                        if (isNaN(target)) return Swal.showValidationMessage('Isi target dengan nominal yang benar');
                        return { target_pengeluaran: target };
                    }
                }).then(async (result) => {
                    if (result.isConfirmed) {
                        try {
                            const response = await fetch('{{ url("user/simpanTargetPengeluaran") }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken,
                                    'X-Requested-With': 'XMLHttpRequest',
                                },
                                body: JSON.stringify(result.value)
                            });
                            const data = await parseJsonResponse(response, 'Gagal menyimpan target budget.');
                            await syncMutationPayload(data);
                            window.showAlert('success', 'Berhasil!', data.message || 'Target pengeluaran berhasil disimpan.');
                        } catch (error) {
                            window.showAlert('error', 'Gagal!', error.message || 'Gagal menyimpan target budget.');
                        }
                    }
                });
            }

            window.showBudgetModal = function () {
                const kategoriDatalist = kategoriList.map(k => `<option value="${k.charAt(0).toUpperCase() + k.slice(1)}">`).join('');
                
                Swal.fire({
                    title: 'Set Budget Kategori',
                    html: `
                        <div class="space-y-6 pt-4 text-left">
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Kategori</label>
                                <div class="relative group">
                                    <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">category</span>
                                    <input id="swal-budget-kategori" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-5 !pl-14 !pr-6 !font-bold !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all text-sm" list="kategoriOptions" placeholder="Cari atau ketik kategori...">
                                </div>
                                <datalist id="kategoriOptions">${kategoriDatalist}</datalist>
                            </div>
                            
                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Limit Budget (Rp)</label>
                                <div class="relative group">
                                    <span class="absolute left-6 top-1/2 -translate-y-1/2 text-primary-500 font-black text-xl">Rp</span>
                                    <input id="swal-budget-nominal" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-5 !pl-16 !pr-6 !font-black !text-xl !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all" type="text" placeholder="0">
                                </div>
                            </div>

                            <div class="space-y-4">
                                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Periode</label>
                                <div class="relative group">
                                    <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">event_repeat</span>
                                    <select id="swal-budget-periode" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-5 !pl-14 !pr-6 !font-bold !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all text-sm !h-14 !appearance-none">
                                        <option value="bulanan">Bulanan</option>
                                        <option value="mingguan">Mingguan</option>
                                        <option value="custom">Custom Range</option>
                                    </select>
                                </div>
                            </div>

                            <div id="custom-date-fields" class="grid grid-cols-2 gap-4 hidden">
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Mulai</label>
                                    <div class="relative group">
                                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">calendar_today</span>
                                        <input id="swal-budget-start" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-4 !pl-12 !pr-4 !font-bold !text-xs !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all" type="date">
                                    </div>
                                </div>
                                <div class="space-y-4">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Selesai</label>
                                    <div class="relative group">
                                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">calendar_today</span>
                                        <input id="swal-budget-end" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-4 !pl-12 !pr-4 !font-bold !text-xs !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all" type="date">
                                    </div>
                                </div>
                            </div>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Simpan Budget',
                    background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                    didOpen: () => {
                        const nominalInput = document.getElementById('swal-budget-nominal');
                        const periodeSelect = document.getElementById('swal-budget-periode');
                        const customFields = document.getElementById('custom-date-fields');

                        nominalInput.addEventListener('input', (e) => {
                            let v = e.target.value.replace(/\D/g, "");
                            e.target.value = v ? new Intl.NumberFormat("id-ID").format(v) : "";
                        });

                        periodeSelect.addEventListener('change', (e) => {
                            if (e.target.value === 'custom') {
                                customFields.classList.remove('hidden');
                            } else {
                                customFields.classList.add('hidden');
                            }
                        });
                    },
                    preConfirm: () => {
                        const kategori = document.getElementById('swal-budget-kategori').value;
                        const nominal = document.getElementById('swal-budget-nominal').value.replace(/\./g, '');
                        const periode = document.getElementById('swal-budget-periode').value;
                        const tanggal_mulai = document.getElementById('swal-budget-start').value;
                        const tanggal_akhir = document.getElementById('swal-budget-end').value;

                        if (!kategori || !nominal) return Swal.showValidationMessage('Isi kategori dan nominal');
                        if (periode === 'custom' && (!tanggal_mulai || !tanggal_akhir)) return Swal.showValidationMessage('Isi tanggal mulai dan selesai');
                        
                        return { kategori, nominal, periode, tanggal_mulai, tanggal_akhir };
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        fetch('{{ url("user/simpanBudgetKategori") }}', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
                            body: JSON.stringify(result.value)
                        }).then(res => res.json()).then(data => {
                            if (data.success) {
                                window.showAlert('success', 'Berhasil!', data.message).then(() => {
                                    location.reload();
                                });
                            } else {
                                window.showAlert('error', 'Gagal!', data.message || 'Gagal menyimpan budget.');
                            }
                        });
                    }
                });
            }

            window.hapusBudget = function (id, label) {
                Swal.fire({
                    title: 'Hapus Budget?',
                    text: `Apakah Anda yakin ingin menghapus budget untuk "${label}"?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#ef4444',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Ya, Hapus',
                    cancelButtonText: 'Batal',
                    background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`{{ url('user/hapusBudgetKategori') }}/${id}`, {
                            method: 'POST',
                            headers: { 'X-CSRF-TOKEN': csrfToken }
                        }).then(res => res.json()).then(data => {
                            if (data.success) {
                                window.showAlert('success', 'Berhasil!', data.message).then(() => {
                                    location.reload();
                                });
                            } else {
                                window.showAlert('error', 'Gagal!', data.message || 'Gagal menghapus budget.');
                            }
                        });
                    }
                });
            }

            // Chart Init (Line + Net Bar)
            const trendCtx = document.getElementById('financeTrendChart');
            const netCtx = document.getElementById('financeNetChart');
            const insightEl = document.getElementById('cashflowInsight');
            const periodButtons = document.querySelectorAll('.cashflow-period-btn');
            const periodLabels = { '7d': '7 hari', '30d': '30 hari', '3m': '3 bulan', '12m': '12 bulan' };
            const toIdr = (value) => 'Rp ' + Number(value || 0).toLocaleString('id-ID');
            const pctText = (value) => (value >= 0 ? '+' : '') + Number(value || 0).toFixed(1) + '%';

            let trendChartInstance = null;
            let netChartInstance = null;

            const renderCashflowCharts = (periodKey) => {
                const series = cashflowSeries[periodKey] || cashflowSeries['30d'];
                if (!series || !series.labels) return;

                if (trendChartInstance) trendChartInstance.destroy();
                if (netChartInstance) netChartInstance.destroy();

                trendChartInstance = new Chart(trendCtx, {
                    type: 'line',
                    data: {
                        labels: series.labels,
                        datasets: [
                            {
                                label: 'Uang Masuk',
                                data: series.income,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16,185,129,0.10)',
                                borderWidth: 3,
                                tension: 0.35,
                                fill: false,
                                pointRadius: 2,
                                pointHoverRadius: 5
                            },
                            {
                                label: 'Uang Keluar',
                                data: series.expense,
                                borderColor: '#ef4444',
                                backgroundColor: 'rgba(239,68,68,0.10)',
                                borderWidth: 3,
                                tension: 0.35,
                                fill: false,
                                pointRadius: 2,
                                pointHoverRadius: 5
                            },
                            {
                                label: 'Selisih Uang',
                                data: series.net,
                                borderColor: '#334155',
                                backgroundColor: 'rgba(51,65,85,0.08)',
                                borderWidth: 3,
                                tension: 0.35,
                                borderDash: [6, 4],
                                fill: false,
                                pointRadius: 2,
                                pointHoverRadius: 5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: true, labels: { boxWidth: 10, usePointStyle: true } },
                            tooltip: {
                                cornerRadius: 12,
                                padding: 12,
                                callbacks: {
                                    label: (ctx) => `${ctx.dataset.label}: ${toIdr(ctx.raw)}`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { weight: 'bold', size: 10 }, color: '#94a3b8' }
                            },
                            y: {
                                grid: {
                                    drawBorder: false,
                                    color: (ctx) => (ctx.tick && ctx.tick.value === 0 ? 'rgba(15,23,42,0.35)' : 'rgba(226,232,240,0.35)')
                                },
                                ticks: {
                                    callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID'),
                                    font: { weight: 'bold', size: 10 },
                                    color: '#94a3b8'
                                }
                            }
                        }
                    }
                });

                netChartInstance = new Chart(netCtx, {
                    type: 'bar',
                    data: {
                        labels: series.labels,
                        datasets: [{
                            label: 'Net',
                            data: series.net,
                            borderRadius: 8,
                            backgroundColor: series.net.map((v) => v >= 0 ? 'rgba(16,185,129,0.8)' : 'rgba(239,68,68,0.8)')
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false },
                            tooltip: {
                                callbacks: {
                                    label: (ctx) => `Net: ${toIdr(ctx.raw)}`
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { weight: 'bold', size: 9 }, color: '#94a3b8' }
                            },
                            y: {
                                grid: { color: 'rgba(226,232,240,0.25)', drawBorder: false },
                                ticks: {
                                    callback: (v) => 'Rp ' + Number(v).toLocaleString('id-ID'),
                                    font: { size: 9, weight: 'bold' },
                                    color: '#94a3b8'
                                }
                            }
                        }
                    }
                });

                if (insightEl) {
                    const directionClass = Number(series.change_pct || 0) >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400';
                    insightEl.innerHTML = `
                        Net <strong>${periodLabels[periodKey] || periodKey}</strong>: <strong>${toIdr(series.total_net)}</strong>.
                        Perubahan vs periode sebelumnya:
                        <strong class="${directionClass}">${pctText(series.change_pct)}</strong>.
                        Uang keluar terbanyak di <strong>${series.max_expense_label || '-'}</strong> sebesar <strong>${toIdr(series.max_expense_value)}</strong>.
                    `;
                }
            };

            periodButtons.forEach((btn) => {
                btn.addEventListener('click', () => {
                    currentCashflowPeriod = btn.getAttribute('data-cashflow-period') || '30d';
                    periodButtons.forEach((item) => {
                        item.classList.remove('bg-primary-500', 'text-white');
                        item.classList.add('bg-slate-100', 'dark:bg-white/10', 'text-slate-500', 'dark:text-slate-300');
                    });
                    btn.classList.remove('bg-slate-100', 'dark:bg-white/10', 'text-slate-500', 'dark:text-slate-300');
                    btn.classList.add('bg-primary-500', 'text-white');
                    renderCashflowCharts(currentCashflowPeriod);
                });
            });


            renderCashflowCharts(currentCashflowPeriod);

            // Realtime Event Listener for Chart/Data refresh if needed

            window.addEventListener('balanceUpdated', () => {
                if (!hasReceivedInitialRealtimeBalance) {
                    hasReceivedInitialRealtimeBalance = true;
                    return;
                }

                if (Date.now() < suppressRealtimeUntil) {
                    return;
                }

                window.syncStatistikSnapshotDebounced();
            });
        });
    </script>
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                document.getElementById('page-skeleton').classList.add('hidden');
                document.getElementById('main-content').classList.remove('hidden');
                document.getElementById('main-content').classList.add('animate-fade-in');
            }, 300); // 300ms for that snappy premium feel
        });
    </script>
@endsection
