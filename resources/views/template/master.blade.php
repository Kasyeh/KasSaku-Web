<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>KasSaku Admin - Smart Finance Hub</title>
  <link rel="icon" href="{{ url('logo-kassaku.svg') }}" type="image/svg+xml">
  <link rel="shortcut icon" href="{{ url('logo-kassaku.svg') }}" type="image/svg+xml">
  <link rel="manifest" href="{{ url('assets/favicon/manifest.json') }}">
  <script src="https://cdn.tailwindcss.com?plugins=forms,typography"></script>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap"
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
            surface: { light: "#FFFFFF", dark: "#1e1e2d" },
            background: { light: "#F8F9FC", dark: "#151521" }
          },
          fontFamily: { sans: ["Plus Jakarta Sans", "sans-serif"] },
          boxShadow: {
            'glow': '0 0 20px rgba(52, 211, 153, 0.15)',
            'card': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 4px 6px -2px rgba(0, 0, 0, 0.05)',
            'premium': '0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04)'
          },
          animation: { 'fade-in': 'fadeIn 0.5s ease-out forwards', 'slide-up': 'slideUp 0.5s ease-out forwards' },
          keyframes: {
            fadeIn: { '0%': { opacity: '0' }, '100%': { opacity: '1' } },
            slideUp: { '0%': { opacity: '0', transform: 'translateY(10px)' }, '100%': { opacity: '1', transform: 'translateY(0)' } },
            shimmer: { '100%': { transform: 'translateX(100%)' } }
          }
        },
      },
    };
  </script>

  <style type="text/tailwindcss">
    @layer utilities {
            .glass-effect { @apply bg-white/70 backdrop-blur-lg border border-white/20 dark:bg-surface-dark/40 dark:border-white/5; }
            .card-premium { @apply bg-surface-light dark:bg-surface-dark border border-slate-100 dark:border-white/5 shadow-card transition-all duration-300; }
            .hover-card { @apply hover:-translate-y-1.5 hover:shadow-premium; }
            .nav-link-active { @apply bg-primary-50 dark:bg-primary-900/10 text-primary-700 dark:text-primary-400 font-bold; }
        }
        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-thumb { @apply bg-slate-200 dark:bg-slate-800 rounded-full; }
        
        #admin-sidebar { transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1); }

        /* Skeleton Shimmer */
        .skeleton { @apply relative overflow-hidden bg-slate-200 dark:bg-slate-800 rounded-lg; }
        .skeleton::after {
            @apply absolute inset-0 content-[''];
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.4), transparent);
            transform: translateX(-100%);
            animation: shimmer 2s infinite;
        }
        .dark .skeleton::after {
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.05), transparent);
        }
    </style>
</head>

<body class="antialiased">
  <div
    class="h-screen overflow-hidden flex bg-background-light dark:bg-background-dark text-slate-800 dark:text-slate-200 font-sans transition-colors duration-300">

    <!-- Admin Sidebar -->
    <aside id="admin-sidebar"
      class="fixed inset-y-0 left-0 z-50 w-72 bg-surface-light dark:bg-surface-dark border-r border-slate-100 dark:border-slate-800 flex flex-col transform -translate-x-full lg:translate-x-0 transition-transform duration-300 shadow-sm">

      <!-- Sidebar Header -->
      <div class="h-20 flex items-center justify-between px-8 border-b border-slate-50 dark:border-slate-800/50">
        <div class="flex items-center">
          <div class="w-8 h-8 rounded-lg overflow-hidden shadow-lg mr-3 shadow-primary-500/20 bg-slate-900">
            <img src="{{ url('logo-kassaku.svg') }}" alt="KasSaku Logo" class="w-full h-full object-cover">
          </div>
          <span class="text-xl font-bold text-slate-900 dark:text-white tracking-tight">Pusat Admin</span>
        </div>
        <button onclick="toggleSidebar()"
          class="lg:hidden p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500">
          <span class="material-icons-round">close</span>
        </button>
      </div>

      <!-- Sidebar Nav -->
      <nav class="flex-1 px-4 py-8 space-y-2 overflow-y-auto">
        <div class="px-4 mb-4 text-[10px] font-black uppercase tracking-widest text-slate-400 opacity-70">Menu Utama
        </div>

        <a href="{{ url('dashboard/admin') }}"
          class="flex items-center px-4 py-3.5 text-sm font-medium rounded-xl transition-all group {{ Request::is('dashboard/admin*') ? 'nav-link-active shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5' }}">
          <span class="material-icons-round mr-3 text-xl transition-transform group-hover:scale-110">dashboard</span>
          Statistik Utama
        </a>

        <a href="{{ url('list_user') }}"
          class="flex items-center px-4 py-3.5 text-sm font-medium rounded-xl transition-all group {{ Request::is('list_user*') ? 'nav-link-active shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5' }}">
          <span class="material-icons-round mr-3 text-xl transition-transform group-hover:scale-110">people</span>
          Kelola Pengguna
        </a>

        <a href="{{ url('motivasi') }}"
          class="flex items-center px-4 py-3.5 text-sm font-medium rounded-xl transition-all group {{ Request::is('motivasi*') ? 'nav-link-active shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5' }}">
          <span
            class="material-icons-round mr-3 text-xl transition-transform group-hover:scale-110">tips_and_updates</span>
          Motivasi
        </a>

        <a href="{{ route('admin.permintaan_unblock') }}"
          class="flex items-center justify-between px-4 py-3.5 text-sm font-medium rounded-xl transition-all group {{ Request::is('admin/permintaan-unblock*') ? 'nav-link-active shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5' }}">
          <div class="flex items-center">
            <span
              class="material-icons-round mr-3 text-xl transition-transform group-hover:scale-110">mark_email_unread</span>
            Permintaan Akses
          </div>
          @if(isset($pendingUnblockCount) && $pendingUnblockCount > 0)
            <span
              class="flex items-center justify-center min-w-[20px] h-5 px-1.5 bg-rose-500 text-white text-[10px] font-black rounded-full animate-pulse shadow-lg shadow-rose-500/30">
              {{ $pendingUnblockCount }}
            </span>
          @endif
        </a>

        <a href="{{ url('admin/feedback') }}"
          class="flex items-center justify-between px-4 py-3.5 text-sm font-medium rounded-xl transition-all group {{ Request::is('admin/feedback*') ? 'nav-link-active shadow-sm' : 'text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5' }}">
          <div class="flex items-center">
            <span class="material-icons-round mr-3 text-xl transition-transform group-hover:scale-110">feedback</span>
            Umpan Balik User
          </div>
          @if(isset($pendingFeedbackCount) && $pendingFeedbackCount > 0)
            <span class="flex items-center justify-center min-w-[20px] h-5 px-1.5 bg-amber-500 text-white text-[10px] font-black rounded-full animate-pulse shadow-lg shadow-amber-500/30">
              {{ $pendingFeedbackCount }}
            </span>
          @endif
        </a>

        <div class="pt-8 px-4 mb-4 text-[10px] font-black uppercase tracking-widest text-slate-400 opacity-70">Sistem
        </div>

        <a href="javascript:void(0)" onclick="confirmLogoutGlobal()"
          class="flex items-center px-4 py-3.5 text-sm font-medium rounded-xl text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-900/10 transition-all group">
          <span class="material-icons-round mr-3 text-xl transition-transform group-hover:translate-x-1">logout</span>
          Keluar Keamanan
        </a>
      </nav>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 lg:pl-72 flex flex-col h-screen overflow-hidden relative">

      <!-- Global Header -->
      <header
        class="h-20 bg-surface-light/80 dark:bg-surface-dark/80 backdrop-blur-md border-b border-slate-100 dark:border-slate-800 flex items-center justify-between px-8 z-40 sticky top-0 transition-all duration-300">
        <div class="flex items-center">
          <button onclick="toggleSidebar()"
            class="lg:hidden mr-4 p-2 rounded-xl hover:bg-slate-100 dark:hover:bg-white/5 text-slate-500">
            <span class="material-icons-round">menu</span>
          </button>
          <div class="flex flex-col">
            <h2 class="text-lg font-bold text-slate-900 dark:text-white leading-tight">
              @yield('page_title', 'Pusat Admin ✨')</h2>
            <p class="text-[10px] uppercase font-black tracking-widest text-slate-400">
              @yield('page_subtitle', 'KasSaku Financial Ecosystem')</p>
          </div>
        </div>

        <div class="flex items-center gap-4">
          <!-- Admin Profile Box -->
          <div
            class="flex items-center gap-3 px-4 py-2 bg-slate-50 dark:bg-white/5 rounded-2xl border border-slate-100 dark:border-white/5 shadow-sm">
            <div
              class="w-8 h-8 rounded-xl bg-primary-500 flex items-center justify-center text-white text-xs font-black shadow-lg shadow-primary-500/20">
              {{ strtoupper(substr(Auth::user()->username ?? 'A', 0, 1)) }}
            </div>
            <div class="hidden sm:flex flex-col">
              <span
                class="text-xs font-bold text-slate-800 dark:text-slate-200">{{ Auth::user()->username ?? 'Administrator' }}</span>
              <span
                class="text-[9px] font-black text-primary-600 dark:text-primary-400 uppercase tracking-tighter">Admin
                Terverifikasi</span>
            </div>
          </div>
        </div>
      </header>

      <!-- Page Content -->
      <main
        class="flex-1 overflow-y-auto p-6 md:p-10 bg-background-light dark:bg-background-dark scroll-smooth relative animate-fade-in">
        @yield('content')
        <!-- Spacer -->
        <div class="h-20"></div>
      </main>
    </div>
  </div>

  <!-- Global Floating Dark Mode Toggle -->
  <div class="fixed bottom-8 right-8 z-[60]">
    <button
      class="bg-slate-900 dark:bg-white text-white dark:text-slate-900 w-14 h-14 rounded-full shadow-2xl flex items-center justify-center transition-all hover:scale-110 active:scale-95 border-4 border-white/10 dark:border-black/5"
      onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')">
      <span class="material-icons-round block dark:hidden">dark_mode</span>
      <span class="material-icons-round hidden dark:block">light_mode</span>
    </button>
  </div>

  <!-- Scripts -->
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script>
    let pendingUnblockToastWatermark = Number("{{ (int) ($pendingUnblockLatestTimestamp ?? 0) }}");
    let pendingUnblockToastBusy = false;

    // Sidebar Toggle
    function toggleSidebar() {
      const sidebar = document.getElementById('admin-sidebar');
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
          text: 'Apakah Anda yakin ingin mengakhiri sesi admin ini?',
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

      async function pollPendingUnblockFeed() {
        if (pendingUnblockToastBusy) {
          return;
        }

        pendingUnblockToastBusy = true;

        try {
          const response = await fetch("{{ route('admin.realtime.pending_unblock') }}", {
            headers: {
              'X-Requested-With': 'XMLHttpRequest',
              'Accept': 'application/json'
            },
            credentials: 'same-origin'
          });

          if (!response.ok) {
            return;
          }

          const payload = await response.json();
          const latestTimestamp = Number(payload?.data?.latest_pending_timestamp ?? 0);
          const latestPending = payload?.data?.latest_pending;

          if (latestTimestamp > pendingUnblockToastWatermark && latestPending) {
            pendingUnblockToastWatermark = latestTimestamp;

            Swal.fire({
              toast: true,
              position: 'top-end',
              icon: 'info',
              title: 'Permintaan Unblock Baru',
              text: `${latestPending.username} meminta unblock akun`,
              showConfirmButton: true,
              confirmButtonText: 'Lihat',
              confirmButtonColor: '#6366f1',
              timer: 10000,
              timerProgressBar: true,
              background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
              color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
            }).then((result) => {
              if (result.isConfirmed) {
                window.location.href = "{{ route('admin.permintaan_unblock') }}";
              }
            });
          }
        } catch (error) {
          console.error('Pending unblock polling failed:', error);
        } finally {
          pendingUnblockToastBusy = false;
        }
      }

    pollPendingUnblockFeed();
    setInterval(pollPendingUnblockFeed, 5000);
  </script>

  <!-- Firebase Generic Setup -->
  <script type="module">
    import { initializeApp, getApp, getApps } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
    import { getDatabase, ref, onChildAdded } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

    // Firebase Config - Should match your project settings
    const firebaseConfig = {
      databaseURL: "https://kassaku-8beb0-default-rtdb.asia-southeast1.firebasedatabase.app",
    };

    // Reuse the existing Firebase app when another page script already initialized it.
    const app = getApps().length ? getApp() : initializeApp(firebaseConfig);
    const db = getDatabase(app);
    let pendingWatermark = Number("{{ (int) ($pendingUnblockLatestTimestamp ?? 0) }}");

    // Listen for new Unblock Requests
    const unblockRequestsRef = ref(db, 'admin/unblock_requests');

    onChildAdded(unblockRequestsRef, (snapshot) => {
      const data = snapshot.val();
      const requestTimestamp = Number(data?.timestamp ?? 0);

      if (data?.status === 'pending' && requestTimestamp > pendingWatermark) {
        pendingWatermark = requestTimestamp;
        pendingUnblockToastWatermark = requestTimestamp;
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'info',
          title: 'Permintaan Unblock Baru',
          text: `${data.username} meminta unblock akun`,
          showConfirmButton: true,
          confirmButtonText: 'Lihat',
          confirmButtonColor: '#6366f1',
          timer: 10000,
          timerProgressBar: true,
          background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
          color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "{{ route('admin.permintaan_unblock') }}";
          }
        });

        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        audio.play().catch(e => console.log('Audio play failed:', e));
      }
    });

    let pendingFeedbackWatermark = Number("{{ (int) ($pendingFeedbackLatestTimestamp ?? 0) }}");
    const feedbackRef = ref(db, 'admin/feedback_notifications');

    onChildAdded(feedbackRef, (snapshot) => {
      const data = snapshot.val();
      const requestTimestamp = Number(data?.timestamp ?? 0);

      if (data?.status === 'pending' && requestTimestamp > pendingFeedbackWatermark) {
        pendingFeedbackWatermark = requestTimestamp;
        Swal.fire({
          toast: true,
          position: 'top-end',
          icon: 'success',
          title: 'Feedback Baru',
          text: `Ada pesan baru dari ${data.username}`,
          showConfirmButton: true,
          confirmButtonText: 'Baca',
          confirmButtonColor: '#f59e0b',
          timer: 10000,
          timerProgressBar: true,
          background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
          color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
        }).then((result) => {
          if (result.isConfirmed) {
            window.location.href = "{{ url('admin/feedback') }}";
          }
        });

        const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
        audio.play().catch(e => console.log('Audio play failed:', e));
      }
    });

  </script>

  @yield('scripts')
</body>

</html>