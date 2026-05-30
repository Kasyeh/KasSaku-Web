@extends('template.auth')

@section('title', 'Daftar')

@section('content')
    <div class="max-w-6xl mx-auto w-full">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
            
            {{-- Left Side: Illustration & Branding (Visible on Desktop) --}}
            <div class="hidden lg:flex flex-1 flex-col items-start space-y-8 animate-fade-in" style="animation-delay: 0.2s;">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl flex items-center justify-center shadow-xl border border-white/20">
                        <img src="{{ url('logo-kassaku.png') }}" alt="KasSaku" class="h-10 w-auto">
                    </div>
                    <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter">KasSaku</span>
                </div>
                
                <div class="space-y-4">
                    <h1 class="text-6xl font-black text-slate-900 dark:text-white leading-[1.1] tracking-tight">
                        Kelola <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-emerald-500">Keuangan</span><br>
                        Jadi Lebih Seru.
                    </h1>
                    <p class="text-xl text-slate-500 dark:text-slate-400 font-medium max-w-md">
                        Bergabunglah dengan ribuan pengguna lainnya yang sudah mulai cerdas finansial bersama KasSaku.
                    </p>
                </div>

                <div class="relative w-full aspect-square max-w-lg">
                    {{-- Floating Glass Cards Background --}}
                    <div class="absolute top-10 -left-10 w-32 h-32 bg-primary-400/20 blur-3xl rounded-full animate-pulse"></div>
                    <div class="absolute bottom-10 -right-10 w-40 h-40 bg-violet-400/20 blur-3xl rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                    
                    <img src="{{ url('assets/img/register-illustration.png') }}" alt="KasSaku Illustration" 
                         class="w-full h-auto drop-shadow-2xl animate-float">
                </div>
            </div>

            {{-- Right Side: Registration Form --}}
            <div class="w-full max-w-md lg:flex-1">
                {{-- Mobile Logo --}}
                <div class="lg:hidden flex justify-center mb-8">
                    <div class="w-20 h-20 rounded-2xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl flex items-center justify-center shadow-xl border border-white/20">
                        <img src="{{ url('logo-kassaku.png') }}" alt="KasSaku" class="h-12 w-auto">
                    </div>
                </div>

                <div class="glass-card rounded-[48px] p-8 md:p-10 w-full relative overflow-hidden group">
                    {{-- Decorative Gradient Glow --}}
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary-500/10 blur-3xl rounded-full group-hover:bg-primary-500/20 transition-colors duration-700"></div>
                    
                    <div class="mb-10 relative">
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Buat Akun</h2>
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Silakan lengkapi data dirimu</p>
                    </div>

                    <form method="POST" action="{{ url('register/action') }}" class="space-y-6" id="registerForm">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Username</label>
                            <div class="relative group/input">
                                <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                                    <span class="material-icons-round text-slate-300 group-focus-within/input:text-primary-500 transition-colors">person</span>
                                </div>
                                <input type="text" name="username" value="{{ old('username') }}" required autofocus
                                    placeholder="Username unik"
                                    class="w-full pl-14 pr-6 py-5 bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-[24px] focus:bg-white dark:focus:bg-white/10 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                            </div>
                            @error('username')
                                <p class="text-[10px] font-bold text-rose-500 ml-4 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Email</label>
                            <div class="relative group/input">
                                <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                                    <span class="material-icons-round text-slate-300 group-focus-within/input:text-primary-500 transition-colors">email</span>
                                </div>
                                <input type="email" name="email" value="{{ old('email') }}" required
                                    placeholder="nama@email.com"
                                    class="w-full pl-14 pr-6 py-5 bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-[24px] focus:bg-white dark:focus:bg-white/10 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                            </div>
                            @error('email')
                                <p class="text-[10px] font-bold text-rose-500 ml-4 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="space-y-4">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Password</label>
                            <div class="relative group/input">
                                <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                                    <span class="material-icons-round text-slate-300 group-focus-within/input:text-primary-500 transition-colors">lock</span>
                                </div>
                                <input type="password" id="password" name="password" required placeholder="••••••••"
                                    class="w-full pl-14 pr-14 py-5 bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-[24px] focus:bg-white dark:focus:bg-white/10 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                                <button type="button" id="togglePassword"
                                    class="absolute right-5 inset-y-0 flex items-center text-slate-300 hover:text-primary-500 transition-colors">
                                    <span class="material-icons-round">visibility</span>
                                </button>
                            </div>
                            
                            {{-- Password Strength Meter --}}
                            <div class="px-2 space-y-3">
                                <div class="h-1.5 w-full bg-slate-100 dark:bg-white/5 rounded-full overflow-hidden">
                                    <div id="strengthBar" class="h-full w-0 transition-all duration-500 ease-out"></div>
                                </div>
                                <div class="grid grid-cols-2 gap-x-4 gap-y-2">
                                    <div class="requirement flex items-center gap-2 text-[10px] font-bold text-slate-400 transition-colors" id="req-length">
                                        <span class="material-icons-round text-[14px]">circle</span> 8+ Karakter
                                    </div>
                                    <div class="requirement flex items-center gap-2 text-[10px] font-bold text-slate-400 transition-colors" id="req-upper">
                                        <span class="material-icons-round text-[14px]">circle</span> Huruf Kapital
                                    </div>
                                    <div class="requirement flex items-center gap-2 text-[10px] font-bold text-slate-400 transition-colors" id="req-digit">
                                        <span class="material-icons-round text-[14px]">circle</span> Angka
                                    </div>
                                    <div class="requirement flex items-center gap-2 text-[10px] font-bold text-slate-400 transition-colors" id="req-symbol">
                                        <span class="material-icons-round text-[14px]">circle</span> Simbol
                                    </div>
                                </div>
                            </div>
                            @error('password')
                                <p class="text-[10px] font-bold text-rose-500 ml-4 mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <button type="submit" id="submitBtn"
                            class="w-full py-5 bg-gradient-to-r from-primary-500 to-emerald-500 hover:from-primary-600 hover:to-emerald-600 text-white rounded-[24px] font-black text-lg shadow-xl shadow-primary-500/25 transition-all active:scale-95 group disabled:opacity-50 disabled:grayscale disabled:cursor-not-allowed">
                            Daftar Sekarang
                            <span class="material-icons-round align-middle ml-2 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </button>
                    </form>
                    
                    <div class="mt-10 text-center">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            Sudah punya akun?
                            <a href="{{ url('/') }}" class="text-primary-600 dark:text-primary-400 hover:underline ml-1">Masuk Sini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <style>
        @keyframes float {
            0% { transform: translateY(0px) rotate(0deg); }
            50% { transform: translateY(-20px) rotate(2deg); }
            100% { transform: translateY(0px) rotate(0deg); }
        }
        .animate-float { animation: float 6s infinite ease-in-out; }
    </style>
@endsection

@section('scripts')
    <script>
        const passwordInput = document.getElementById('password');
        const togglePassword = document.getElementById('togglePassword');
        const submitBtn = document.getElementById('submitBtn');
        const strengthBar = document.getElementById('strengthBar');
 
        // Toggle Password Visibility
        togglePassword.addEventListener('click', () => {
            const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
            passwordInput.setAttribute('type', type);
            togglePassword.querySelector('.material-icons-round').textContent = type === 'password' ? 'visibility' : 'visibility_off';
        });
 
        // Requirements Validation & Strength Meter
        passwordInput.addEventListener('input', () => {
            const val = passwordInput.value;
            const checks = {
                length: val.length >= 8,
                upper: /[A-Z]/.test(val),
                digit: /[0-9]/.test(val),
                symbol: /[!@#$%^&*_]/.test(val)
            };
 
            updateReq('req-length', checks.length);
            updateReq('req-upper', checks.upper);
            updateReq('req-digit', checks.digit);
            updateReq('req-symbol', checks.symbol);
 
            // Update Strength Bar
            const passedCount = Object.values(checks).filter(Boolean).length;
            const percentage = (passedCount / 4) * 100;
            strengthBar.style.width = percentage + '%';
            
            // Bar Colors
            if (passedCount <= 1) strengthBar.className = 'h-full w-0 transition-all duration-500 ease-out bg-rose-500';
            else if (passedCount <= 2) strengthBar.className = 'h-full w-0 transition-all duration-500 ease-out bg-amber-500';
            else if (passedCount <= 3) strengthBar.className = 'h-full w-0 transition-all duration-500 ease-out bg-blue-500';
            else strengthBar.className = 'h-full w-0 transition-all duration-500 ease-out bg-emerald-500';
 
            const allOk = Object.values(checks).every(Boolean);
            submitBtn.disabled = !allOk;
        });
 
        function updateReq(id, isMet) {
            const el = document.getElementById(id);
            const icon = el.querySelector('.material-icons-round');
 
            if (isMet) {
                el.classList.remove('text-slate-400');
                el.classList.add('text-emerald-500');
                icon.textContent = 'check_circle';
                icon.classList.add('scale-110');
            } else {
                el.classList.add('text-slate-400');
                el.classList.remove('text-emerald-500');
                icon.textContent = 'circle';
                icon.classList.remove('scale-110');
            }
        }
    </script>
@endsection