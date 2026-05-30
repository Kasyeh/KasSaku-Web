<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') - KasSaku</title>
    <link rel="icon" href="{{ url('logo-kassaku.png') }}" type="image/png">
    <link rel="shortcut icon" href="{{ url('logo-kassaku.png') }}" type="image/png">
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
                        sky: { 500: '#0ea5e9' },
                        violet: { 500: '#8b5cf6' }
                    },
                    fontFamily: { sans: ["Plus Jakarta Sans", "sans-serif"] },
                },
            },
        };
    </script>

    <style type="text/tailwindcss">
        @layer utilities {
            .glass-card { 
                @apply bg-white/70 backdrop-blur-3xl border border-white/40 dark:bg-slate-900/60 dark:border-white/10 shadow-2xl; 
            }
            .aurora-bg { @apply relative overflow-hidden bg-slate-50 dark:bg-[#0b0f1a] min-h-screen flex items-center justify-center p-6 transition-colors duration-500; }
            
            .blob {
                @apply absolute rounded-full mix-blend-multiply filter blur-[100px] opacity-40 animate-aurora dark:mix-blend-screen dark:opacity-20;
            }
        }

        @keyframes aurora {
            0% { transform: translate(0, 0) scale(1) rotate(0deg); }
            33% { transform: translate(100px, -150px) scale(1.2) rotate(120deg); }
            66% { transform: translate(-80px, 80px) scale(0.8) rotate(240deg); }
            100% { transform: translate(0, 0) scale(1) rotate(360deg); }
        }

        .animate-aurora { animation: aurora 25s infinite alternate ease-in-out; }
        .animate-fade-in { animation: fadeIn 1s ease-out forwards; }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
    </style>
</head>

<body class="antialiased">
    <div class="aurora-bg">
        {{-- Aurora Blobs (Premium Effect) --}}
        <div class="blob w-[600px] h-[600px] bg-primary-400/60 -top-40 -left-40" style="animation-duration: 30s;"></div>
        <div class="blob w-[500px] h-[500px] bg-sky-500/50 top-1/4 -right-40"
            style="animation-delay: -5s; animation-duration: 35s;"></div>
        <div class="blob w-[700px] h-[700px] bg-violet-500/40 -bottom-60 left-1/4"
            style="animation-delay: -10s; animation-duration: 40s;"></div>
        <div class="blob w-[400px] h-[400px] bg-emerald-400/40 bottom-1/4 right-1/4"
            style="animation-delay: -15s; animation-duration: 32s;"></div>

        {{-- Floating Dark Mode Toggle --}}
        <div class="fixed top-8 right-8 z-[100]">
            <button
                class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl text-slate-800 dark:text-white w-12 h-12 rounded-2xl shadow-xl flex items-center justify-center transition-all hover:scale-110 active:scale-95 border border-white/20 dark:border-white/5"
                onclick="document.documentElement.classList.toggle('dark'); localStorage.setItem('theme', document.documentElement.classList.contains('dark') ? 'dark' : 'light')">
                <span class="material-icons-round block dark:hidden">dark_mode</span>
                <span class="material-icons-round hidden dark:block">light_mode</span>
            </button>
        </div>

        <div class="relative z-10 w-full animate-fade-in">
            @yield('content')
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Auto dark mode from system preference / localStorage
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

        @if(session('success'))
            window.showAlert('success', 'Berhasil!', {!! json_encode(session('success')) !!});
        @endif

        @if(session('error'))
            window.showAlert('error', 'Gagal!', {!! json_encode(session('error')) !!});
        @endif

        @if(session('alert'))
            window.showAlert('info', 'Informasi', {!! json_encode(session('alert')) !!});
        @endif
    </script>
    @yield('scripts')
</body>

</html>
