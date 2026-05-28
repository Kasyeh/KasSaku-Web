<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KasSaku</title>
  <link rel="icon" href="{{ url('logo-kassaku.svg') }}" type="image/svg+xml">
  <link rel="shortcut icon" href="{{ url('logo-kassaku.svg') }}" type="image/svg+xml">
  <link rel="manifest" href="{{ url('assets/favicon/manifest.json') }}">
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:ital,wght@0,400;0,700;1,400&family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
    rel="stylesheet" />
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />

  <script>
    tailwind.config = {
      darkMode: "class",
      theme: {
        extend: {
          colors: {
            primary: { 50: '#ecfdf5', 100: '#d1fae5', 200: '#a7f3d0', 300: '#6ee7b7', 400: '#34d399', 500: '#10b981', 600: '#059669', 700: '#047857', 800: '#065f46', 900: '#064e3b' },
            secondary: { 50: '#eff6ff', 100: '#dbeafe', 200: '#bfdbfe', 300: '#93c5fd', 400: '#60a5fa', 500: '#3b82f6', 600: '#2563eb', 700: '#1d4ed8', 800: '#1e40af', 900: '#1e3a8a' },
            surface: { light: "#ffffff", dark: "#1e1e2d" },
            background: { light: "#fbfcfd", dark: "#151521" }
          },
          fontFamily: { sans: ["Plus Jakarta Sans", "sans-serif"] },
          boxShadow: {
            'glow': '0 0 20px rgba(16, 185, 129, 0.08)',
            'card': '0 10px 30px -5px rgba(0, 0, 0, 0.04), 0 4px 6px -2px rgba(0, 0, 0, 0.01)',
            'premium': '0 25px 50px -12px rgba(0, 0, 0, 0.06)'
          },
          animation: { 'fade-in': 'fadeIn 0.5s ease-out forwards', 'slide-up': 'slideUp 0.5s ease-out forwards' },
          keyframes: {
            fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
            slideUp: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            shimmer: {
              '100%': { transform: 'translateX(100%)' }
            }
          }
        },
      },
    };
  </script>

  <style type="text/tailwindcss">
    @layer utilities {
            .glass-effect { @apply bg-white/80 backdrop-blur-2xl border border-white/60 dark:bg-surface-dark/40 dark:border-white/5; }
            .card-premium { @apply bg-white dark:bg-surface-dark border border-slate-100/80 dark:border-white/5 shadow-card transition-all duration-500; }
            .hover-card { @apply hover:-translate-y-1 hover:shadow-premium hover:bg-white; }
            
            /* Premium Nav Link */
            .nav-link-active { 
                @apply bg-primary-50 dark:bg-primary-500/10 text-primary-600 dark:text-primary-400 shadow-[0_0_20px_rgba(16,185,129,0.1)]; 
            }
            .nav-link-inactive { @apply text-slate-500 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 hover:text-slate-900 dark:hover:text-white; }
            
            .text-glow-primary { text-shadow: 0 0 15px rgba(16, 185, 129, 0.4); }
            .bg-glow-primary { box-shadow: 0 0 25px rgba(16, 185, 129, 0.2); }
            
            .premium-gradient { @apply bg-gradient-to-br from-primary-500 to-emerald-600; }

            /* Filter Chips */
            .chip-filter {
                @apply px-6 py-2.5 rounded-2xl text-[10px] font-black uppercase tracking-widest transition-all duration-300;
                @apply bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-400 border border-transparent;
                @apply hover:bg-slate-200 dark:hover:bg-white/10 hover:text-slate-900 dark:hover:text-white;
            }
            .chip-filter.active {
                @apply bg-primary-500 text-white shadow-lg shadow-primary-500/30 border-primary-400;
            }
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { @apply bg-slate-200 dark:bg-slate-800 rounded-full hover:bg-slate-300 transition-colors; }
        
        /* Mobile Menu Transitions */
        #sidebar { transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Skeleton Shimmer */
        .skeleton {
            @apply relative overflow-hidden bg-slate-200 dark:bg-slate-800 rounded-lg;
        }
        .skeleton::after {
            @apply absolute inset-0 content-[''];
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: translateX(-100%);
            animation: shimmer 2s infinite;
        }
        .dark .skeleton::after {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
        }

        @keyframes float {
            0% { transform: translateY(0px); }
            50% { transform: translateY(-5px); }
            100% { transform: translateY(0px); }
        }
        .animate-float-sm { animation: float 4s infinite ease-in-out; }
    </style>
</head>

<body class="antialiased">
  <div
    class="h-screen overflow-hidden flex bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 font-sans transition-colors duration-300">

    <!-- Sidebar -->
    <aside id="sidebar"
      class="fixed inset-y-0 left-0 z-50 w-72 bg-surface-light dark:bg-surface-dark border-r border-slate-100 dark:border-slate-800 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 shadow-sm">

      <!-- Sidebar Header -->
      <div class="h-24 flex items-center justify-between px-8 border-b border-slate-50 dark:border-white/5">
        <div class="flex items-center">
          <div class="w-11 h-11 rounded-2xl overflow-hidden shadow-lg mr-3 bg-slate-950 flex items-center justify-center p-2 relative group transition-transform duration-500 hover:rotate-6">
            <div class="absolute inset-0 bg-primary-500/20 blur-xl opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
            <img src="{{ url('logo-kassaku.svg') }}" alt="KasSaku Logo" class="w-full h-full object-contain relative z-10 animate-float-sm">
          </div>
          <span class="text-2xl font-black text-slate-900 dark:text-white tracking-tighter">KasSaku</span>
        </div>
        <button onclick="toggleSidebar()"
          class="lg:hidden p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500">
          <span class="material-icons-round">close</span>
        </button>
      </div>

      <!-- Sidebar Nav -->
      <nav class="flex-1 px-4 py-6 space-y-8 overflow-y-auto custom-scrollbar">
        <!-- Main Menu Section -->
        <div>
          <p class="px-4 mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400/80">Menu Utama</p>
          <div class="space-y-1.5">
            <a href="{{ url('user/home') }}"
              class="flex items-center px-5 py-3.5 text-sm font-bold rounded-2xl transition-all duration-300 group {{ Request::is('user/home*') ? 'nav-link-active' : 'nav-link-inactive' }}">
              <div class="relative flex items-center">
                <span class="material-icons-round mr-4 text-[24px] transition-transform duration-500 group-hover:scale-110 {{ Request::is('user/home*') ? 'text-primary-500' : '' }}">dashboard</span>
                @if(Request::is('user/home*'))
                  <span class="absolute -left-6 w-1.5 h-6 bg-primary-500 rounded-r-full shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                @endif
              </div>
              Beranda
            </a>

            <a href="{{ url('user/statistik') }}"
              class="flex items-center px-5 py-3.5 text-sm font-bold rounded-2xl transition-all duration-300 group {{ Request::is('user/statistik*') ? 'nav-link-active' : 'nav-link-inactive' }}">
              <div class="relative flex items-center">
                <span class="material-icons-round mr-4 text-[24px] transition-transform duration-500 group-hover:scale-110 {{ Request::is('user/statistik*') ? 'text-primary-500' : '' }}">analytics</span>
                @if(Request::is('user/statistik*'))
                  <span class="absolute -left-6 w-1.5 h-6 bg-primary-500 rounded-r-full shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                @endif
              </div>
              Statistik
            </a>

            <a href="{{ url('user/riwayat') }}"
              class="flex items-center px-5 py-3.5 text-sm font-bold rounded-2xl transition-all duration-300 group {{ Request::is('user/riwayat*') ? 'nav-link-active' : 'nav-link-inactive' }}">
              <div class="relative flex items-center">
                <span class="material-icons-round mr-4 text-[24px] transition-transform duration-500 group-hover:scale-110 {{ Request::is('user/riwayat*') ? 'text-primary-500' : '' }}">history_edu</span>
                @if(Request::is('user/riwayat*'))
                  <span class="absolute -left-6 w-1.5 h-6 bg-primary-500 rounded-r-full shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                @endif
              </div>
              Catatan
            </a>

            <a href="{{ url('user/impian') }}"
              class="flex items-center px-5 py-3.5 text-sm font-bold rounded-2xl transition-all duration-300 group {{ Request::is('user/impian*') ? 'nav-link-active' : 'nav-link-inactive' }}">
              <div class="relative flex items-center">
                <span class="material-icons-round mr-4 text-[24px] transition-transform duration-500 group-hover:scale-110 {{ Request::is('user/impian*') ? 'text-primary-500' : '' }}">stars</span>
                @if(Request::is('user/impian*'))
                  <span class="absolute -left-6 w-1.5 h-6 bg-primary-500 rounded-r-full shadow-[0_0_10px_rgba(16,185,129,0.5)]"></span>
                @endif
              </div>
              Tabungan
            </a>
          </div>
        </div>

        <!-- Account Section -->
        <div>
          <p class="px-4 mb-4 text-[10px] font-black uppercase tracking-[0.2em] text-slate-400/80">Akun & Sesi</p>
          <div class="space-y-1.5">
            <a href="{{ url('user/profile') }}"
              class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl transition-all duration-300 group {{ Request::is('user/profile*') ? 'nav-link-active' : 'nav-link-inactive' }}">
              <div class="relative flex items-center">
                <span class="material-icons-round mr-3 text-[22px] transition-transform duration-500 group-hover:scale-110 {{ Request::is('user/profile*') ? 'text-primary-500' : '' }}">person</span>
                @if(Request::is('user/profile*'))
                  <span class="absolute -left-4 w-1 h-5 bg-primary-500 rounded-r-full"></span>
                @endif
              </div>
              Profil Saya
            </a>

            <a href="javascript:void(0)" onclick="confirmLogoutGlobal()"
              class="flex items-center px-4 py-3 text-sm font-bold rounded-2xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-all duration-300 group">
              <span class="material-icons-round mr-3 text-[22px] transition-transform duration-500 group-hover:translate-x-1">logout</span>
              Keluar Sesi
            </a>
          </div>
        </div>
      </nav>

      <!-- Sidebar Footer (User Card) -->
      <div class="p-4 border-t border-slate-50 dark:border-slate-800/50">
        <div class="p-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl overflow-hidden bg-gradient-to-br from-primary-500 to-emerald-600 flex items-center justify-center text-white shadow-lg shadow-primary-500/20">
            @if(Auth::user()->avatar)
              <img src="{{ Auth::user()->avatar }}" class="w-full h-full object-cover" alt="Avatar">
            @else
              <span class="text-sm font-black">{{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}</span>
            @endif
          </div>
          <div class="flex-1 min-w-0">
            <p class="text-xs font-bold text-slate-900 dark:text-white truncate">{{ Auth::user()->username ?? 'User' }}</p>
            <p class="text-[10px] text-slate-500 dark:text-slate-400 truncate">Sesi Aktif</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-72 flex flex-col h-screen overflow-hidden relative">

      <!-- Global Header -->
      <header
        class="h-24 bg-surface-light/80 dark:bg-background-dark/80 backdrop-blur-2xl border-b border-slate-100 dark:border-white/5 flex items-center justify-between px-8 z-40 sticky top-0 transition-all duration-300">
        <div class="flex items-center">
          <button onclick="toggleSidebar()"
            class="lg:hidden mr-4 w-11 h-11 flex items-center justify-center rounded-2xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500 transition-colors">
            <span class="material-icons-round">menu</span>
          </button>
          <div class="flex flex-col">
            <h2 class="text-xl font-black text-slate-900 dark:text-white leading-tight tracking-tight">@yield('page_title', 'KasSaku ✨')
            </h2>
            <p class="text-[10px] uppercase font-black tracking-[0.2em] text-primary-600/80 dark:text-primary-400/80">
              @yield('page_subtitle', 'Pencatat Keuangan Pintar')</p>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <button id="notification-bell-button" type="button"
            class="relative group w-12 h-12 rounded-2xl border border-slate-200/70 dark:border-white/10 bg-white/70 dark:bg-white/5 backdrop-blur-xl flex items-center justify-center text-slate-600 dark:text-slate-200 hover:-translate-y-0.5 hover:shadow-premium transition-all duration-300">
            <span
              class="material-icons-round transition-transform duration-300 group-hover:scale-110">notifications</span>
            <span id="notification-bell-badge"
              class="{{ ($unreadNotificationCount ?? 0) > 0 ? '' : 'hidden ' }}absolute -top-1 -right-1 min-w-[22px] h-[22px] px-1.5 rounded-full bg-gradient-to-r from-rose-500 to-orange-400 text-white text-[10px] font-black flex items-center justify-center shadow-lg shadow-rose-500/30">
              {{ min((int) ($unreadNotificationCount ?? 0), 99) }}
            </span>
          </button>

          <!-- User Mini Info -->
          <div
            class="hidden md:flex items-center gap-3 px-4 py-2 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5">
            <div
              class="w-8 h-8 rounded-xl overflow-hidden bg-primary-500 flex items-center justify-center text-white text-xs font-black">
              @if(Auth::user()->avatar)
                <img src="{{ Auth::user()->avatar }}" class="w-full h-full object-cover" alt="Avatar">
              @else
                {{ strtoupper(substr(Auth::user()->username ?? 'U', 0, 1)) }}
              @endif
            </div>
            <span
              class="text-xs font-bold text-slate-700 dark:text-slate-200">{{ Auth::user()->username ?? 'User' }}</span>
          </div>

          <div id="notification-panel-overlay"
            class="pointer-events-none fixed inset-0 z-[65] hidden bg-slate-950/40 backdrop-blur-[2px] opacity-0 transition-opacity duration-300">
          </div>

          <aside id="notification-panel"
            class="fixed top-0 right-0 z-[70] h-full w-full max-w-md translate-x-full transition-transform duration-300 ease-out">
            <div
              class="h-screen max-h-screen bg-white/92 dark:bg-[#12131d]/92 backdrop-blur-2xl border-l border-slate-200/70 dark:border-white/10 shadow-2xl shadow-slate-950/20 flex flex-col overflow-hidden">
              <div class="px-6 py-6 border-b border-slate-200/70 dark:border-white/10">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <p class="text-[10px] font-black uppercase tracking-[0.25em] text-slate-400">Notification Center</p>
                    <h3 class="mt-2 text-2xl font-black text-slate-900 dark:text-white tracking-tight">Riwayat
                      notifikasi</h3>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Semua pengingat dan alert terbaru akan
                      muncul di sini.</p>
                  </div>
                  <button id="notification-panel-close" type="button"
                    class="w-11 h-11 rounded-2xl bg-slate-100 dark:bg-white/5 text-slate-500 dark:text-slate-300 flex items-center justify-center hover:bg-slate-200 dark:hover:bg-white/10 transition-colors">
                    <span class="material-icons-round">close</span>
                  </button>
                </div>

                <div class="mt-6 grid grid-cols-2 gap-3">
                  <div class="rounded-3xl bg-gradient-to-br from-slate-900 to-slate-700 text-white p-4 shadow-xl">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-white/60 font-black">Belum dibaca</p>
                    <p id="notification-summary-unread" class="mt-3 text-3xl font-black tracking-tight">
                      {{ (int) ($unreadNotificationCount ?? 0) }}
                    </p>
                  </div>
                  <div
                    class="rounded-3xl bg-gradient-to-br from-emerald-400 via-primary-500 to-secondary-500 text-white p-4 shadow-xl">
                    <p class="text-[10px] uppercase tracking-[0.2em] text-white/70 font-black">Status</p>
                    <p class="mt-3 text-lg font-black tracking-tight">Sinkron aktif</p>
                    <p class="mt-2 text-xs text-white/80">Notifikasi baru akan otomatis tercatat setelah terkirim.</p>
                  </div>
                </div>

                <div class="mt-5 flex items-center justify-between gap-3">
                  <div class="flex items-center gap-2">
                    <span
                      class="inline-flex items-center gap-2 rounded-full bg-slate-100 dark:bg-white/5 px-3 py-1.5 text-[11px] font-bold text-slate-500 dark:text-slate-300">
                      <span class="w-2 h-2 rounded-full bg-emerald-400"></span>
                      Live archive
                    </span>
                  </div>
                  <button id="notification-mark-read" type="button"
                    class="inline-flex items-center gap-2 rounded-full bg-slate-900 dark:bg-white text-white dark:text-slate-900 px-4 py-2 text-xs font-black tracking-wide hover:scale-[1.02] transition-transform">
                    <span class="material-icons-round text-base">done_all</span>
                    Tandai dibaca
                  </button>
                </div>
              </div>

              <div id="notification-panel-body" class="flex-1 overflow-y-auto px-4 py-4 min-h-0">
                <div id="notification-list-loading" class="space-y-3">
                  <div class="h-24 rounded-[28px] skeleton"></div>
                  <div class="h-24 rounded-[28px] skeleton"></div>
                  <div class="h-24 rounded-[28px] skeleton"></div>
                </div>

                <div id="notification-empty-state" class="hidden h-full min-h-[320px] flex items-center justify-center">
                  <div class="text-center max-w-xs mx-auto">
                    <div
                      class="w-20 h-20 mx-auto rounded-[28px] bg-gradient-to-br from-slate-900 to-slate-700 text-white flex items-center justify-center shadow-xl">
                      <span class="material-icons-round text-4xl">notifications_none</span>
                    </div>
                    <h4 class="mt-6 text-xl font-black text-slate-900 dark:text-white">Belum ada notifikasi</h4>
                    <p class="mt-2 text-sm text-slate-500 dark:text-slate-400">Setelah reminder, alert transaksi, atau
                      notifikasi admin terkirim, riwayatnya akan muncul di panel ini.</p>
                  </div>
                </div>

                <div id="notification-list" class="space-y-3"></div>
              </div>
            </div>
          </aside>
        </div>
      </header>

      <!-- Page Content -->
      <main class="flex-1 overflow-y-auto p-6 md:p-10 bg-background-light dark:bg-background-dark scroll-smooth">
        @yield('content')
        <!-- Extra space at bottom -->
        <div class="h-20"></div>
      </main>
    </div>
  </div>

  <style>
    .scrollbar-none::-webkit-scrollbar { display: none; }
    .scrollbar-none { -ms-overflow-style: none; scrollbar-width: none; }
  </style>

  <!-- Floating Chatbot Widget -->
  <div class="fixed bottom-24 right-8 z-[60] flex flex-col items-end">
    <!-- Chat Container -->
    <div id="chatbot-container" class="hidden w-[360px] h-[500px] bg-white/95 dark:bg-slate-900/95 backdrop-blur-2xl border border-slate-200/50 dark:border-white/10 rounded-[30px] shadow-2xl flex flex-col overflow-hidden mb-4 transition-all duration-300 transform translate-y-4 opacity-0 scale-95 origin-bottom-right">
      <!-- Header -->
      <div class="p-4 bg-gradient-to-r from-indigo-600 via-purple-600 to-pink-600 text-white flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-white/10 backdrop-blur-md flex items-center justify-center">
            <span class="material-icons-round">smart_toy</span>
          </div>
          <div>
            <h4 class="text-xs font-black tracking-wide">Asisten KasSaku</h4>
            <div class="flex items-center gap-1.5 mt-0.5">
              <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
              <span class="text-[8px] font-bold text-white/80 uppercase tracking-widest">Online</span>
            </div>
          </div>
        </div>
        <div class="flex items-center gap-1">
          <button onclick="resetChat()" title="Reset Percakapan" class="w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center transition-colors">
            <span class="material-icons-round text-sm">refresh</span>
          </button>
          <button onclick="toggleChatbot()" class="w-8 h-8 rounded-lg hover:bg-white/10 flex items-center justify-center transition-colors">
            <span class="material-icons-round text-sm">close</span>
          </button>
        </div>
      </div>

      <!-- Messages -->
      <div id="chatbot-messages" class="flex-1 p-4 overflow-y-auto space-y-3 scroll-smooth text-xs">
        <div class="flex gap-2.5 max-w-[85%]">
          <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
            <span class="material-icons-round text-base">smart_toy</span>
          </div>
          <div class="bg-slate-100 dark:bg-white/5 p-3 rounded-2xl rounded-tl-none text-slate-800 dark:text-slate-200 leading-relaxed">
            Halo! Saya asisten keuangan KasSaku. Ada yang bisa saya bantu hari ini?
          </div>
        </div>
      </div>

      <!-- Suggestion Chips -->
      <div class="px-4 py-2 border-t border-slate-100 dark:border-white/5 flex gap-2 overflow-x-auto whitespace-nowrap scrollbar-none scroll-smooth">
        <button onclick="sendSuggestion('Berapa saldo saya?')" class="px-3 py-1.5 bg-slate-50 dark:bg-white/5 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border border-slate-200/50 dark:border-white/5 hover:border-indigo-200 dark:hover:border-indigo-500/20 text-[10px] text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold rounded-full transition-all">
          Cek Saldo
        </button>
        <button onclick="sendSuggestion('Bagaimana pengeluaran saya bulan ini?')" class="px-3 py-1.5 bg-slate-50 dark:bg-white/5 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border border-slate-200/50 dark:border-white/5 hover:border-indigo-200 dark:hover:border-indigo-500/20 text-[10px] text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold rounded-full transition-all">
          Histori Belanja
        </button>
        <button onclick="sendSuggestion('Berikan tips hemat')" class="px-3 py-1.5 bg-slate-50 dark:bg-white/5 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border border-slate-200/50 dark:border-white/5 hover:border-indigo-200 dark:hover:border-indigo-500/20 text-[10px] text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold rounded-full transition-all">
          Tips Hemat
        </button>
        <button onclick="sendSuggestion('Bantuan')" class="px-3 py-1.5 bg-slate-50 dark:bg-white/5 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 border border-slate-200/50 dark:border-white/5 hover:border-indigo-200 dark:hover:border-indigo-500/20 text-[10px] text-slate-600 dark:text-slate-400 hover:text-indigo-600 dark:hover:text-indigo-400 font-bold rounded-full transition-all">
          Bantuan
        </button>
      </div>

      <!-- Input Form -->
      <form id="chatbot-form" onsubmit="handleChatSubmit(event)" class="p-3 border-t border-slate-100 dark:border-white/5 bg-slate-50/50 dark:bg-slate-900/50 flex gap-2">
        <input type="text" id="chatbot-input" placeholder="Tulis pesan..." autocomplete="off" class="flex-1 bg-white dark:bg-slate-800 border border-slate-200/50 dark:border-white/5 rounded-xl px-4 py-2.5 text-xs text-slate-800 dark:text-white placeholder-slate-400 focus:outline-none focus:border-indigo-500 focus:ring-1 focus:ring-indigo-500 transition-all">
        <button type="submit" class="w-10 h-10 bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl flex items-center justify-center shadow-lg shadow-indigo-600/20 transition-all active:scale-95">
          <span class="material-icons-round text-base">send</span>
        </button>
      </form>
    </div>

    <!-- Toggle Button -->
    <button id="chatbot-toggle-btn" onclick="toggleChatbot()" class="bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 hover:scale-105 active:scale-95 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center text-white transition-all relative group">
      <span class="absolute inset-0 rounded-full bg-gradient-to-br from-indigo-500 via-purple-600 to-pink-500 blur-md opacity-50 group-hover:opacity-80 transition-opacity"></span>
      <span class="material-icons-round text-xl relative z-10 block group-hover:rotate-12 transition-transform">smart_toy</span>
    </button>
  </div>

  <!-- Global Floating Dark Mode Toggle -->
  <div class="fixed bottom-8 right-8 z-[60]">
    <button
      class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center transition-all hover:scale-110 active:scale-95"
      onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')">
      <span class="material-icons-round block dark:hidden">dark_mode</span>
      <span class="material-icons-round hidden dark:block">light_mode</span>
    </button>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.6.0/dist/confetti.browser.min.js"></script>
  <script>
    function toggleChatbot() {
        const container = document.getElementById('chatbot-container');
        const btnIcon = document.querySelector('#chatbot-toggle-btn .material-icons-round');
        
        if (container.classList.contains('hidden')) {
            container.classList.remove('hidden');
            setTimeout(() => {
                container.classList.remove('opacity-0', 'translate-y-4', 'scale-95');
                container.classList.add('opacity-100', 'translate-y-0', 'scale-100');
            }, 10);
            btnIcon.innerText = 'close';
        } else {
            container.classList.remove('opacity-100', 'translate-y-0', 'scale-100');
            container.classList.add('opacity-0', 'translate-y-4', 'scale-95');
            setTimeout(() => {
                container.classList.add('hidden');
            }, 300);
            btnIcon.innerText = 'smart_toy';
        }
    }

    function addMessage(text, isUser = false) {
        const messagesContainer = document.getElementById('chatbot-messages');
        const msgDiv = document.createElement('div');
        
        if (isUser) {
            msgDiv.className = 'flex justify-end gap-2.5 max-w-[85%] ml-auto';
            msgDiv.innerHTML = `
                <div class="bg-indigo-600 text-white p-3 rounded-2xl rounded-tr-none leading-relaxed">
                    ${escapeHtml(text)}
                </div>
            `;
        } else {
            msgDiv.className = 'flex gap-2.5 max-w-[85%]';
            msgDiv.innerHTML = `
                <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                    <span class="material-icons-round text-base">smart_toy</span>
                </div>
                <div class="bg-slate-100 dark:bg-white/5 p-3 rounded-2xl rounded-tl-none text-slate-800 dark:text-slate-200 leading-relaxed whitespace-pre-wrap">
                    ${text}
                </div>
            `;
        }
        
        messagesContainer.appendChild(msgDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function addLoadingIndicator() {
        const messagesContainer = document.getElementById('chatbot-messages');
        const loaderDiv = document.createElement('div');
        loaderDiv.id = 'chatbot-loader';
        loaderDiv.className = 'flex gap-2.5 max-w-[85%]';
        loaderDiv.innerHTML = `
            <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                <span class="material-icons-round text-base">smart_toy</span>
            </div>
            <div class="bg-slate-100 dark:bg-white/5 p-3 rounded-2xl rounded-tl-none text-slate-500 flex items-center gap-1">
                <span class="w-1.5 h-1.5 bg-slate-400 dark:bg-slate-500 rounded-full animate-bounce" style="animation-delay: 0ms;"></span>
                <span class="w-1.5 h-1.5 bg-slate-400 dark:bg-slate-500 rounded-full animate-bounce" style="animation-delay: 150ms;"></span>
                <span class="w-1.5 h-1.5 bg-slate-400 dark:bg-slate-500 rounded-full animate-bounce" style="animation-delay: 300ms;"></span>
            </div>
        `;
        messagesContainer.appendChild(loaderDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    function removeLoadingIndicator() {
        const loader = document.getElementById('chatbot-loader');
        if (loader) {
            loader.remove();
        }
    }

    function escapeHtml(text) {
        return text
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    async function handleChatSubmit(e) {
        if (e) e.preventDefault();
        
        const input = document.getElementById('chatbot-input');
        const message = input.value.trim();
        if (!message) return;
        
        input.value = '';
        addMessage(message, true);
        
        addLoadingIndicator();
        
        try {
            const response = await fetch("{{ route('user.chatbot.ask') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ message: message })
            });
            
            const data = await response.json();
            removeLoadingIndicator();
            
            if (data.success && data.reply) {
                addMessage(data.reply, false);
            } else {
                addMessage('Maaf, asisten sedang sibuk. Silakan coba lagi nanti.', false);
            }
        } catch (error) {
            removeLoadingIndicator();
            addMessage('Koneksi internet bermasalah. Silakan periksa jaringan Anda.', false);
            console.error('Chatbot error:', error);
        }
    }

    function sendSuggestion(text) {
        const input = document.getElementById('chatbot-input');
        input.value = text;
        handleChatSubmit();
    }

    async function resetChat() {
        if (!confirm('Apakah Anda yakin ingin mereset riwayat chat asisten?')) return;
        
        try {
            const response = await fetch("{{ route('user.chatbot.reset') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            });
            
            const data = await response.json();
            if (data.success) {
                const messagesContainer = document.getElementById('chatbot-messages');
                messagesContainer.innerHTML = `
                    <div class="flex gap-2.5 max-w-[85%]">
                      <div class="w-8 h-8 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center shrink-0">
                        <span class="material-icons-round text-base">smart_toy</span>
                      </div>
                      <div class="bg-slate-100 dark:bg-white/5 p-3 rounded-2xl rounded-tl-none text-slate-800 dark:text-slate-200 leading-relaxed">
                        Halo! Saya asisten keuangan KasSaku. Ada yang bisa saya bantu hari ini?
                      </div>
                    </div>
                `;
            }
        } catch (error) {
            console.error('Reset chatbot error:', error);
        }
    }

    // Animated Number Counter Utility
    function animateNumber(element, start, end, duration) {
        if (!element) return;
        const startTime = performance.now();
        
        // Easing function (easeOutExpo) for a smooth slow-down effect
        const easeOutExpo = (t) => t === 1 ? 1 : 1 - Math.pow(2, -10 * t);
        
        function update(currentTime) {
            const elapsed = currentTime - startTime;
            const progress = Math.min(elapsed / duration, 1);
            const easeProgress = easeOutExpo(progress);
            
            const current = Math.floor(start + (end - start) * easeProgress);
            element.innerText = new Intl.NumberFormat('id-ID').format(current);
            
            if (progress < 1) {
                requestAnimationFrame(update);
            } else {
                element.innerText = new Intl.NumberFormat('id-ID').format(end);
            }
        }
        
        requestAnimationFrame(update);
    }
    
    // Store previous values for animation
    window.previousBalances = {
        saldo: 0,
        pemasukan: 0,
        pengeluaran: 0,
        target_pengeluaran: 0
    };

    let accountStatusPollingBusy = false;
    let forcedLogoutInProgress = false;

    // Sidebar Toggle
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.toggle('-translate-x-full');
    }

    // Initialize Theme
    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
      document.documentElement.classList.add('dark');
    } else {
      document.documentElement.classList.remove('dark');
    }

    // Global Alert Helper
    window.showAlert = function (icon, title, text) {
      return Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        text: text,
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
        color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
      });
    }

    window.kasSakuGuide = {
      storageKey(guideId) {
        return `kassaku_guide_${guideId}_dismissed`;
      },
      initGuide(guideId) {
        const card = document.querySelector(`[data-guide-id="${guideId}"]`);
        if (!card) return;

        const key = this.storageKey(guideId);
        const isDismissed = localStorage.getItem(key) === 'true';

        if (isDismissed) {
          card.classList.add('hidden');
        }

        document.querySelectorAll(`[data-guide-dismiss="${guideId}"]`).forEach(button => {
          button.addEventListener('click', () => {
            card.classList.add('hidden');
            localStorage.setItem(key, 'true');
          });
        });

        document.querySelectorAll(`[data-guide-open="${guideId}"]`).forEach(button => {
          button.addEventListener('click', () => {
            card.classList.remove('hidden');
          });
        });
      }
    };

    // Global Session Alerts
    @if(session('success'))
      showAlert('success', 'Berhasil!', "{{ session('success') }}");
    @endif

    @if(session('error'))
      showAlert('error', 'Gagal!', "{{ session('error') }}");
    @endif

      // Global Alert Logic
      function confirmLogoutGlobal() {
        Swal.fire({
          title: 'Konfirmasi Keluar',
          text: 'Apakah Anda yakin ingin mengakhiri sesi ini?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonColor: '#10b981',
          cancelButtonColor: '#ef4444',
          confirmButtonText: 'Ya, Keluar',
          cancelButtonText: 'Batal',
          background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
          color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "{{ url('actionLogout') }}";
          }
        })
      }

    function forceBlockedLogout() {
      if (forcedLogoutInProgress) {
        return;
      }

      forcedLogoutInProgress = true;
      Swal.fire({
        title: 'Sesi Berakhir',
        text: 'Akun Anda telah dinonaktifkan oleh admin.',
        icon: 'warning',
        confirmButtonColor: '#10b981',
        confirmButtonText: 'OK',
        background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
        color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
        allowOutsideClick: false
      }).then(() => {
        window.location.href = "{{ url('actionLogout') }}";
      });
    }

    async function pollAccountStatusFallback() {
      if (accountStatusPollingBusy) {
        return;
      }

      accountStatusPollingBusy = true;

      try {
        const response = await fetch("{{ route('user.realtime.account_status') }}", {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          cache: 'no-store'
        });

        if (!response.ok) {
          if (response.status === 401 || response.status === 403) {
            const payload = await response.json().catch(() => null);
            if (payload?.data?.force_logout || payload?.content?.force_logout || response.status === 401 || response.status === 403) {
              forceBlockedLogout();
            }
          }
          return;
        }

        const payload = await response.json();
        if (Number(payload?.data?.active ?? 0) !== 1) {
          forceBlockedLogout();
        }
      } catch (error) {
        console.error('Account status polling failed:', error);
      } finally {
        accountStatusPollingBusy = false;
      }
    }

    pollAccountStatusFallback();
    setInterval(pollAccountStatusFallback, 5000);

    const notificationPanel = document.getElementById('notification-panel');
    const notificationOverlay = document.getElementById('notification-panel-overlay');
    const notificationBellButton = document.getElementById('notification-bell-button');
    const notificationBellBadge = document.getElementById('notification-bell-badge');
    const notificationPanelClose = document.getElementById('notification-panel-close');
    const notificationList = document.getElementById('notification-list');
    const notificationLoading = document.getElementById('notification-list-loading');
    const notificationEmptyState = document.getElementById('notification-empty-state');
    const notificationSummaryUnread = document.getElementById('notification-summary-unread');
    const notificationMarkReadButton = document.getElementById('notification-mark-read');
    let notificationsLoadedOnce = false;

    function notificationCategoryTheme(accent) {
      const themes = {
        emerald: {
          ring: 'from-emerald-400/20 to-emerald-500/5',
          chip: 'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300',
          icon: 'bg-emerald-500 text-white'
        },
        amber: {
          ring: 'from-amber-400/20 to-orange-500/5',
          chip: 'bg-amber-50 text-amber-600 dark:bg-amber-500/10 dark:text-amber-300',
          icon: 'bg-amber-500 text-white'
        },
        rose: {
          ring: 'from-rose-400/20 to-orange-500/5',
          chip: 'bg-rose-50 text-rose-600 dark:bg-rose-500/10 dark:text-rose-300',
          icon: 'bg-rose-500 text-white'
        },
        sky: {
          ring: 'from-sky-400/20 to-secondary-500/5',
          chip: 'bg-sky-50 text-sky-600 dark:bg-sky-500/10 dark:text-sky-300',
          icon: 'bg-sky-500 text-white'
        },
        violet: {
          ring: 'from-violet-400/20 to-fuchsia-500/5',
          chip: 'bg-violet-50 text-violet-600 dark:bg-violet-500/10 dark:text-violet-300',
          icon: 'bg-violet-500 text-white'
        },
        slate: {
          ring: 'from-slate-300/20 to-slate-500/5',
          chip: 'bg-slate-100 text-slate-600 dark:bg-white/5 dark:text-slate-300',
          icon: 'bg-slate-700 text-white'
        }
      };

      return themes[accent] || themes.slate;
    }

    function renderNotificationItems(items) {
      const container = document.getElementById('notification-list');
      const loader = document.getElementById('notification-list-loading');
      const emptyState = document.getElementById('notification-empty-state');

      if (!container) return;

      container.innerHTML = '';
      if (loader) loader.classList.add('hidden');

      if (!items || !items.length) {
        if (emptyState) emptyState.classList.remove('hidden');
        return;
      }

      if (emptyState) emptyState.classList.add('hidden');

      items.forEach((item, index) => {
        try {
          const accentName = item.accent || 'slate';
          const theme = notificationCategoryTheme(accentName);
          const row = document.createElement('div');
          const categoryDisplay = (item.category || 'Notifikasi').replace(/_/g, ' ');

          row.className = `relative mb-3 rounded-2xl border ${item.read ? 'border-slate-100 bg-slate-50/50 opacity-80' : 'border-slate-200 bg-white'} p-4 shadow-sm hover:shadow-md transition-all duration-300`;

          row.innerHTML = `
            <div class="flex items-start gap-4">
              <div class="shrink-0 w-10 h-10 rounded-xl ${theme.icon || 'bg-slate-500 text-white'} flex items-center justify-center shadow-sm">
                <span class="material-icons-round text-lg">${item.icon || 'notifications'}</span>
              </div>
              <div class="min-w-0 flex-1">
                <div class="flex justify-between items-start gap-3">
                  <div class="min-w-0 flex-1">
                    <h4 class="text-sm font-bold text-slate-800 leading-tight truncate">${item.title || 'Notifikasi'}</h4>
                    <p class="mt-1 text-xs text-slate-500 leading-relaxed line-clamp-2">${item.excerpt || ''}</p>
                  </div>
                  <span class="text-[10px] font-medium text-slate-400 whitespace-nowrap pt-0.5">${item.sent_at_human || ''}</span>
                </div>
                <div class="mt-3 flex items-center gap-2">
                  <span class="px-2 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider ${theme.chip || 'bg-slate-100'}">${categoryDisplay}</span>
                  ${!item.read ? '<span class="flex h-2 w-2 rounded-full bg-blue-500"></span>' : ''}
                </div>
              </div>
            </div>
          `;
          container.appendChild(row);
        } catch (e) {
          console.error('Render error:', e, item);
        }
      });
    }

    function setNotificationUnreadCount(count) {
      const safeCount = Number(count || 0);
      notificationSummaryUnread.textContent = safeCount;
      notificationBellBadge.textContent = safeCount > 99 ? '99+' : safeCount;
      notificationBellBadge.classList.toggle('hidden', safeCount < 1);
    }

    async function loadNotifications() {
      notificationLoading.classList.remove('hidden');
      notificationEmptyState.classList.add('hidden');

      try {
        const response = await fetch("{{ route('user.notifications.index') }}", {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
          },
          credentials: 'same-origin',
          cache: 'no-store'
        });

        if (!response.ok) {
          throw new Error('Failed to fetch notifications');
        }

        const payload = await response.json();
        const data = payload?.data || {};
        renderNotificationItems(data.items || []);
        setNotificationUnreadCount(data.unread_count || 0);
        notificationsLoadedOnce = true;
      } catch (error) {
        notificationLoading.classList.add('hidden');
        notificationEmptyState.classList.remove('hidden');
        console.error('Notification fetch failed:', error);
      }
    }

    async function markAllNotificationsAsRead() {
      try {
        const response = await fetch("{{ route('user.notifications.read_all') }}", {
          method: 'POST',
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': "{{ csrf_token() }}",
            'Accept': 'application/json'
          },
          credentials: 'same-origin'
        });

        if (!response.ok) {
          throw new Error('Failed to mark notifications as read');
        }

        await loadNotifications();
      } catch (error) {
        console.error('Mark notifications as read failed:', error);
      }
    }

    function openNotificationPanel() {
      notificationOverlay.classList.remove('hidden', 'pointer-events-none');
      requestAnimationFrame(() => {
        notificationOverlay.classList.remove('opacity-0');
        notificationPanel.classList.remove('translate-x-full');
      });

      document.body.classList.add('overflow-hidden');
      loadNotifications();
    }

    function closeNotificationPanel() {
      notificationOverlay.classList.add('pointer-events-none');
      notificationOverlay.classList.add('opacity-0');
      notificationPanel.classList.add('translate-x-full');
      document.body.classList.remove('overflow-hidden');
      setTimeout(() => notificationOverlay.classList.add('hidden'), 300);
    }

    notificationBellButton?.addEventListener('click', openNotificationPanel);
    notificationPanelClose?.addEventListener('click', closeNotificationPanel);
    notificationOverlay?.addEventListener('click', closeNotificationPanel);
    notificationMarkReadButton?.addEventListener('click', markAllNotificationsAsRead);
  </script>
  <script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
    import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

    const firebaseConfig = {
      databaseURL: "https://kassaku-8beb0-default-rtdb.asia-southeast1.firebasedatabase.app",
    };

    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);
    const userId = "{{ Auth::id() }}";
    const initialActiveStatus = Number("{{ (int) (Auth::user()->active ?? 0) }}");

    if (userId) {
      const accountEventRef = ref(db, `users/${userId}/account_event`);

      const balanceRef = ref(db, `users/${userId}/balance`);
      console.log(`[Firebase] Listening to balance at: users/${userId}/balance`);

      onValue(balanceRef, (snapshot) => {
        const data = snapshot.val();
        console.log('[Firebase] Balance data received:', data);
        
        if (data !== null) {
          window.dispatchEvent(new CustomEvent('balanceUpdated', { detail: data }));
          const formatIDR = (val) => new Intl.NumberFormat('id-ID').format(val);

            if (data.saldo !== undefined) {
            const handledByPage = typeof window.handleRealtimeBalanceUpdate === 'function'
              ? window.handleRealtimeBalanceUpdate(data.saldo)
              : false;

            if (!handledByPage) {
              document.querySelectorAll('.rt-balance').forEach(el => {
                animateNumber(el, window.previousBalances.saldo, data.saldo, 1500);
              });
            }
            window.previousBalances.saldo = data.saldo;
          }
          if (data.pemasukan !== undefined) {
            document.querySelectorAll('.rt-pemasukan').forEach(el => {
              animateNumber(el, window.previousBalances.pemasukan, data.pemasukan, 1500);
            });
            window.previousBalances.pemasukan = data.pemasukan;
          }
          if (data.pengeluaran !== undefined) {
            document.querySelectorAll('.rt-pengeluaran').forEach(el => {
              animateNumber(el, window.previousBalances.pengeluaran, data.pengeluaran, 1500);
            });
            window.previousBalances.pengeluaran = data.pengeluaran;
          }
          if (data.target_pengeluaran !== undefined) {
            document.querySelectorAll('.rt-target-pengeluaran').forEach(el => {
              animateNumber(el, window.previousBalances.target_pengeluaran, data.target_pengeluaran || 0, 1500);
            });
            window.previousBalances.target_pengeluaran = data.target_pengeluaran || 0;
          }
        }
      }, (error) => {
        console.error('[Firebase] Balance listener error:', error);
      });

      // Listen for Account Status (Auto-Logout)
      // Compare against the status rendered by Laravel so a blocked session
      // is also logged out after refresh/reconnect, not only on subsequent changes.
      const statusRef = ref(db, `users/${userId}/status/active`);
      let statusFirstLoad = true;
      onValue(statusRef, (snapshot) => {
        const active = Number(snapshot.val());
        if (statusFirstLoad) {
          statusFirstLoad = false;
          if (initialActiveStatus === 1 && active === 0) {
            forceBlockedLogout();
          }
          return;
        }
        if (active === 0) {
          // Force logout — admin just blocked this user
          forceBlockedLogout();
        }
      });

      onValue(accountEventRef, (snapshot) => {
        const data = snapshot.val();
        if (!data) {
          return;
        }

        if (data.event === 'blocked') {
          forceBlockedLogout();
        }
      });
    }
  </script>
  @yield('scripts')
</body>

</html>