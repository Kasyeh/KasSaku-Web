@extends('template.auth')

@section('title', 'Masuk')

@section('content')
    <div class="max-w-6xl mx-auto w-full">
        <div class="flex flex-col lg:flex-row items-center gap-12 lg:gap-20">
            
            {{-- Left Side: Illustration & Branding --}}
            <div class="hidden lg:flex flex-1 flex-col items-start space-y-8 animate-fade-in" style="animation-delay: 0.2s;">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl flex items-center justify-center shadow-xl border border-white/20">
                        <img src="{{ url('logo-kassaku.png') }}" alt="KasSaku" class="h-10 w-auto">
                    </div>
                    <span class="text-3xl font-black text-slate-900 dark:text-white tracking-tighter">KasSaku</span>
                </div>
                
                <div class="space-y-4">
                    <h1 class="text-6xl font-black text-slate-900 dark:text-white leading-[1.1] tracking-tight">
                        Selamat <span class="text-transparent bg-clip-text bg-gradient-to-r from-violet-500 to-sky-500">Datang</span><br>
                        Kembali.
                    </h1>
                    <p class="text-xl text-slate-500 dark:text-slate-400 font-medium max-w-md">
                        Siap untuk mencatat dan mengoptimalkan pengeluaranmu hari ini? Ayo lanjut kelola KasSaku-mu!
                    </p>
                </div>

                <div class="relative w-full aspect-square max-w-lg">
                    <div class="absolute top-10 -left-10 w-32 h-32 bg-violet-400/20 blur-3xl rounded-full animate-pulse"></div>
                    <div class="absolute bottom-10 -right-10 w-40 h-40 bg-sky-400/20 blur-3xl rounded-full animate-pulse" style="animation-delay: 1s;"></div>
                    
                    <img src="{{ url('assets/img/login-illustration.png') }}" alt="KasSaku Login Illustration" 
                         class="w-full h-auto drop-shadow-2xl animate-float">
                </div>
            </div>

            {{-- Right Side: Login Form --}}
            <div class="w-full max-w-md lg:flex-1">
                {{-- Mobile Logo --}}
                <div class="lg:hidden flex justify-center mb-8">
                    <div class="w-20 h-20 rounded-2xl bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl flex items-center justify-center shadow-xl border border-white/20">
                        <img src="{{ url('logo-kassaku.png') }}" alt="KasSaku" class="h-12 w-auto">
                    </div>
                </div>

                <div class="glass-card rounded-[48px] p-8 md:p-10 w-full relative overflow-hidden group">
                    <div class="absolute -top-24 -right-24 w-48 h-48 bg-primary-500/10 blur-3xl rounded-full group-hover:bg-primary-500/20 transition-colors duration-700"></div>
                    
                    <div class="mb-10 relative">
                        <h2 class="text-3xl font-black text-slate-900 dark:text-white tracking-tight mb-2">Masuk ke Akun</h2>
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">Kelola Uangmu dengan Mudah</p>
                    </div>

                    @if (session('error'))
                        <div class="mb-6 p-4 {{ session('rejected_unblock') ? 'bg-amber-500/10 border-amber-500/20' : 'bg-rose-500/10 border-rose-500/20' }} border rounded-2xl animate-shake">
                            <div class="flex items-center justify-center gap-2 mb-2">
                                @if(session('pending_unblock'))
                                    <span class="material-icons-round text-amber-500 animate-pulse">hourglass_top</span>
                                    <p class="text-xs font-bold text-amber-600">Menunggu Tinjauan</p>
                                @elseif(session('rejected_unblock'))
                                    <span class="material-icons-round text-rose-500">cancel</span>
                                    <p class="text-xs font-bold text-rose-500">Permintaan Ditolak</p>
                                @else
                                    <span class="material-icons-round text-rose-500">block</span>
                                    <p class="text-xs font-bold text-rose-500">Akun Diblokir</p>
                                @endif
                            </div>
                            <p class="text-xs font-medium text-center {{ (session('rejected_unblock') || session('blocked')) && !session('pending_unblock') ? 'text-rose-600 dark:text-rose-400' : 'text-amber-700 dark:text-amber-400' }}">
                                {{ session('error') }}
                            </p>
                            @if(session('blocked') && !session('pending_unblock'))
                                <div class="mt-4 flex justify-center">
                                    <button type="button" onclick="showUnblockModal('{{ session('blocked_user_id') }}')"
                                        class="px-4 py-2 {{ session('rejected_unblock') ? 'bg-amber-500 hover:bg-amber-600' : 'bg-rose-500 hover:bg-rose-600' }} text-white text-[10px] font-black rounded-xl transition-all uppercase tracking-widest">
                                        {{ session('rejected_unblock') ? 'Ajukan Permintaan Baru' : 'Minta unblock' }}
                                    </button>
                                </div>
                            @endif
                        </div>
                    @endif

                    <form method="POST" action="{{ url('actionLogin') }}" class="space-y-6" autocomplete="off">
                        @csrf
                        <div class="space-y-2">
                            <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Username</label>
                            <div class="relative group/input">
                                <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                                    <span class="material-icons-round text-slate-300 group-focus-within/input:text-primary-500 transition-colors">person</span>
                                </div>
                                <input type="text" name="username" required autofocus placeholder="Masukkan username"
                                    autocomplete="off" value="{{ old('username') }}"
                                    class="w-full pl-14 pr-6 py-5 bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-[24px] focus:bg-white dark:focus:bg-white/10 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                            </div>
                        </div>

                        <div class="space-y-2">
                            <div class="flex items-center justify-between ml-2">
                                <label class="text-[11px] font-black text-slate-400 uppercase tracking-[0.2em]">Password</label>
                                <button type="button" onclick="showForgotPasswordModal()" class="text-[10px] font-black text-primary-600 dark:text-primary-400 hover:tracking-widest transition-all">Lupa Password?</button>
                            </div>
                            <div class="relative group/input">
                                <div class="absolute inset-y-0 left-5 flex items-center pointer-events-none">
                                    <span class="material-icons-round text-slate-300 group-focus-within/input:text-primary-500 transition-colors">lock</span>
                                </div>
                                <input type="password" id="password" name="password" required placeholder="••••••••"
                                    autocomplete="new-password"
                                    class="w-full pl-14 pr-14 py-5 bg-slate-50/50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-[24px] focus:bg-white dark:focus:bg-white/10 focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                                <button type="button" id="togglePassword"
                                    class="absolute right-5 inset-y-0 flex items-center text-slate-300 hover:text-primary-500 transition-colors">
                                    <span class="material-icons-round">visibility</span>
                                </button>
                            </div>
                            @error('login')
                                <p class="text-[10px] font-bold text-rose-500 ml-4 mt-1 animate-shake">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="flex items-center ml-2 pb-2">
                            <label class="flex items-center gap-3 cursor-pointer group">
                                <input type="checkbox" name="remember" class="w-5 h-5 rounded-lg border-slate-200 dark:border-white/10 text-primary-500 focus:ring-primary-500/20 bg-white dark:bg-white/5 transition-all">
                                <span class="text-xs font-bold text-slate-400 group-hover:text-slate-600 dark:group-hover:text-slate-200 transition-colors">Ingat Saya</span>
                            </label>
                        </div>

                        <button type="submit"
                            class="w-full py-5 bg-gradient-to-r from-primary-500 to-emerald-500 hover:from-primary-600 hover:to-emerald-600 text-white rounded-[24px] font-black text-lg shadow-xl shadow-primary-500/25 transition-all active:scale-95 group">
                            Masuk Sekarang
                            <span class="material-icons-round align-middle ml-2 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                        </button>
                    </form>

                    {{-- Social Divider --}}
                    <div class="my-8 flex items-center gap-4">
                        <div class="h-px flex-1 bg-slate-100 dark:bg-white/5"></div>
                        <span class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Atau</span>
                        <div class="h-px flex-1 bg-slate-100 dark:bg-white/5"></div>
                    </div>

                    {{-- Google Login Button --}}
                    <a href="{{ route('google.login') }}" 
                       class="w-full py-4 flex items-center justify-center gap-3 bg-white dark:bg-white/5 border border-slate-100 dark:border-white/5 hover:bg-slate-50 dark:hover:bg-white/10 rounded-[24px] shadow-sm hover:shadow-md transition-all active:scale-95 group">
                        <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" class="w-6 h-6">
                        <span class="text-sm font-bold text-slate-700 dark:text-slate-200">Masuk dengan Google</span>
                    </a>

                    <div class="mt-10 text-center">
                        <p class="text-sm font-bold text-slate-500 dark:text-slate-400">
                            Belum punya akun?
                            <a href="{{ route('register') }}" class="text-primary-600 dark:text-primary-400 hover:underline ml-1">Daftar Sini</a>
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

    {{-- Forgot Password Modal --}}
    <div id="forgotPasswordModal" class="fixed inset-0 z-50 hidden flex items-center justify-center bg-slate-900/80 opacity-0 transition-opacity duration-300">
        <div class="glass-card rounded-3xl p-8 w-full max-w-md mx-4 transform scale-95 transition-transform duration-300 shadow-2xl relative border border-white/20 dark:border-white/10" id="forgotPasswordModalContent">
            
            {{-- Close Button --}}
            <button onclick="closeForgotPasswordModal()" class="absolute top-4 right-4 w-8 h-8 flex items-center justify-center rounded-full bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-rose-500 hover:bg-rose-50 dark:hover:bg-rose-500/10 transition-colors">
                <span class="material-icons-round text-lg">close</span>
            </button>

            {{-- Step 1: Input Email --}}
            <div id="fp-step-1" class="block">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-primary-500/10 flex items-center justify-center mx-auto mb-4 border border-primary-500/20">
                        <span class="material-icons-round text-primary-500 text-3xl">mark_email_read</span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Lupa Password?</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Masukkan username dan email yang sama seperti saat registrasi agar OTP dikirim ke inbox Anda.</p>
                </div>
                <div class="space-y-4">
                    <div class="relative group">
                        <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">person</span>
                        <input type="text" id="fp-username" placeholder="Username akun" autocomplete="username"
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                    </div>
                    <div class="relative group">
                        <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">email</span>
                        <input type="email" id="fp-email" placeholder="nama@gmail.com" autocomplete="email"
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                    </div>
                    <p id="fp-error-1" class="text-[10px] font-bold text-rose-500 hidden text-center animate-shake"></p>
                    <button type="button" onclick="sendOtp()" id="btn-send-otp"
                        class="w-full py-4 mt-2 bg-primary-500 hover:bg-primary-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-primary-500/30 transition-all active:scale-95 flex justify-center items-center relative overflow-hidden group">
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:animate-[shimmer_1.5s_infinite]"></div>
                        <span id="text-send-otp" class="relative z-10 flex items-center">
                            Kirim OTP <span class="material-icons-round text-sm ml-2">send</span>
                        </span>
                        <span id="spinner-send-otp" class="material-icons-round animate-spin ml-2 hidden relative z-10">refresh</span>
                    </button>
                </div>
            </div>

            {{-- Step 2: Input OTP --}}
            <div id="fp-step-2" class="hidden">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-primary-500/10 flex items-center justify-center mx-auto mb-4 border border-primary-500/20">
                        <span class="material-icons-round text-primary-500 text-3xl">pin</span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Verifikasi OTP</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400 leading-relaxed">Masukkan 6 digit kode yang dikirim ke <br><span id="display-email" class="font-bold text-primary-500"></span></p>
                </div>
                <div class="space-y-4">
                    <input type="text" id="fp-otp" maxlength="6" placeholder="000000"
                        class="w-full py-5 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-black text-2xl tracking-[0.5em] text-center text-slate-800 dark:text-white">
                    <p id="fp-error-2" class="text-[10px] font-bold text-rose-500 hidden text-center animate-shake"></p>
                    <button type="button" onclick="verifyOtp()" id="btn-verify-otp"
                        class="w-full py-4 bg-primary-500 hover:bg-primary-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-primary-500/30 transition-all active:scale-95 flex justify-center items-center group">
                        Verifikasi OTP <span class="material-icons-round text-sm ml-2 group-hover:translate-x-1 transition-transform">arrow_forward</span>
                    </button>
                    <button type="button" onclick="goToStep1()" class="w-full py-3 text-xs font-bold text-slate-400 hover:text-slate-600 dark:hover:text-slate-200 transition-colors">
                        Ubah username / email
                    </button>
                </div>
            </div>

            {{-- Step 3: Reset Password --}}
            <div id="fp-step-3" class="hidden">
                <div class="text-center mb-6">
                    <div class="w-16 h-16 rounded-full bg-emerald-500/10 flex items-center justify-center mx-auto mb-4 border border-emerald-500/20">
                        <span class="material-icons-round text-emerald-500 text-3xl">vpn_key</span>
                    </div>
                    <h2 class="text-2xl font-black text-slate-800 dark:text-white mb-2 tracking-tight">Password Baru</h2>
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-400">Buat password baru yang aman.</p>
                </div>
                <div class="space-y-4">
                    <div class="relative group">
                        <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">lock</span>
                        <input type="password" id="fp-password" placeholder="Password Baru (Min. 8 Karakter)"
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl focus:ring-2 focus:ring-emerald-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                    </div>
                    <div class="relative group">
                        <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-emerald-500 transition-colors">lock</span>
                        <input type="password" id="fp-password-confirm" placeholder="Konfirmasi Password"
                            class="w-full pl-12 pr-4 py-4 bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl focus:ring-2 focus:ring-emerald-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white">
                    </div>
                    <p id="fp-error-3" class="text-[10px] font-bold text-rose-500 hidden text-center animate-shake"></p>
                    <button type="button" onclick="resetPassword()" id="btn-reset-password"
                        class="w-full py-4 mt-2 bg-emerald-500 hover:bg-emerald-600 text-white rounded-2xl font-black text-sm shadow-xl shadow-emerald-500/30 transition-all active:scale-95 flex justify-center items-center relative overflow-hidden group">
                        <div class="absolute inset-0 w-full h-full bg-gradient-to-r from-transparent via-white/20 to-transparent translate-x-[-100%] group-hover:animate-[shimmer_1.5s_infinite]"></div>
                        <span id="text-reset-password" class="relative z-10 flex items-center">
                            Simpan Password <span class="material-icons-round text-sm ml-2">check_circle</span>
                        </span>
                        <span id="spinner-reset-password" class="material-icons-round animate-spin ml-2 hidden relative z-10">refresh</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
@endsection

@section('scripts')
    {{-- Hidden form for unblock request submission --}}
    <div id="unblock-form-container" style="display:none;">
        <form id="unblock-request-form" action="{{ route('mintaUnblock') }}" method="POST">
            @csrf
            <input type="hidden" name="id_user" id="unblock-user-id">
            <input type="hidden" name="pesan" id="unblock-pesan">
        </form>
    </div>

    <script>
        function normalizeForgotOtp(raw) {
            return String(raw || '').replace(/\D/g, '').slice(0, 6);
        }

        function parseForgotPasswordResponse(response) {
            return response.text().then(function (text) {
                let data = {};
                try {
                    data = text ? JSON.parse(text) : {};
                } catch (e) {
                    data = { success: false, message: 'Respons server tidak valid.' };
                }
                if (data.errors && typeof data.errors === 'object') {
                    const first = Object.values(data.errors)[0];
                    data.success = false;
                    data.message = Array.isArray(first) ? first[0] : (data.message || 'Validasi gagal.');
                }
                if (!response.ok && !data.message) {
                    data.success = false;
                    data.message = 'Permintaan gagal (' + response.status + ').';
                }
                return data;
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Password Visibility Toggle
            const togglePassword = document.getElementById('togglePassword');
            const passwordField = document.getElementById('password');

            if (togglePassword && passwordField) {
                const icon = togglePassword.querySelector('.material-icons-round');
                togglePassword.addEventListener('click', () => {
                    const type = passwordField.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordField.setAttribute('type', type);
                    icon.textContent = type === 'password' ? 'visibility' : 'visibility_off';
                });
            }

            // Trigger Alert if Login Failed
            @if($errors->has('login'))
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal',
                    text: 'Username atau password yang Anda masukkan salah.',
                    confirmButtonColor: '#f43f5e',
                    background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                    padding: '2rem',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl px-4 py-2 font-bold uppercase'
                    }
                });
            @endif

            @if(session('registered_success'))
                Swal.fire({
                    icon: 'success',
                    title: 'Pendaftaran Berhasil',
                    text: {!! json_encode(session('registered_success')) !!},
                    confirmButtonColor: '#10b981',
                    background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                    color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                    customClass: {
                        popup: 'rounded-3xl',
                        confirmButton: 'rounded-xl px-4 py-2 font-bold uppercase'
                    }
                });
            @endif

            const fpUsernameEl = document.getElementById('fp-username');
            const fpEmailEl = document.getElementById('fp-email');
            const fpOtpEl = document.getElementById('fp-otp');
            if (fpOtpEl) {
                fpOtpEl.addEventListener('input', function () {
                    const normalized = normalizeForgotOtp(fpOtpEl.value);
                    if (fpOtpEl.value !== normalized) fpOtpEl.value = normalized;
                });
            }
            const fpSubmitOnEnter = function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    sendOtp();
                }
            };
            if (fpUsernameEl) fpUsernameEl.addEventListener('keypress', fpSubmitOnEnter);
            if (fpEmailEl) fpEmailEl.addEventListener('keypress', fpSubmitOnEnter);
            if (fpOtpEl) {
                fpOtpEl.addEventListener('keypress', function (e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        verifyOtp();
                    }
                });
            }
        });

        /**
         * Show modal to request account unblock
         */
        function showUnblockModal(userId) {
            Swal.fire({
                title: 'Minta Unblock Akun',
                text: 'Berikan alasan singkat mengapa akun Anda harus diunblock.',
                input: 'textarea',
                inputPlaceholder: 'Tulis pesan Anda di sini...',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                confirmButtonText: 'Kirim Permintaan',
                cancelButtonText: 'Batal',
                background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                customClass: {
                    popup: 'rounded-3xl',
                    confirmButton: 'rounded-xl px-6 py-2.5 font-bold uppercase tracking-wider',
                    cancelButton: 'rounded-xl px-6 py-2.5 font-bold uppercase tracking-wider'
                },
                preConfirm: (pesan) => {
                    if (!pesan) {
                        Swal.showValidationMessage('Alasan wajib diisi');
                    }
                    return pesan;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    const userIdField = document.getElementById('unblock-user-id');
                    const pesanField = document.getElementById('unblock-pesan');
                    const form = document.getElementById('unblock-request-form');

                    if (userIdField && pesanField && form) {
                        userIdField.value = userId;
                        pesanField.value = result.value;
                        form.submit();
                    }
                }
            });
        }

        /**
         * Forgot Password Modal Logic
         */
        let forgotPasswordEmail = '';
        let forgotPasswordUsername = '';

        function showForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.remove('hidden');
            setTimeout(() => {
                document.getElementById('forgotPasswordModal').classList.remove('opacity-0');
                document.getElementById('forgotPasswordModalContent').classList.remove('scale-95');
                document.getElementById('forgotPasswordModalContent').classList.add('scale-100');
            }, 10);
            goToStep1();
        }

        function closeForgotPasswordModal() {
            document.getElementById('forgotPasswordModal').classList.add('opacity-0');
            document.getElementById('forgotPasswordModalContent').classList.add('scale-95');
            document.getElementById('forgotPasswordModalContent').classList.remove('scale-100');
            setTimeout(() => {
                document.getElementById('forgotPasswordModal').classList.add('hidden');
            }, 300);
        }

        function showStep(stepNumber) {
            document.getElementById('fp-step-1').classList.add('hidden');
            document.getElementById('fp-step-2').classList.add('hidden');
            document.getElementById('fp-step-3').classList.add('hidden');
            document.getElementById(`fp-step-${stepNumber}`).classList.remove('hidden');
        }

        function goToStep1() {
            showStep(1);
            forgotPasswordEmail = '';
            forgotPasswordUsername = '';
            document.getElementById('fp-error-1').classList.add('hidden');
            document.getElementById('fp-otp').value = '';
            document.getElementById('fp-password').value = '';
            document.getElementById('fp-password-confirm').value = '';
        }

        function setButtonLoading(btnId, isLoading) {
            const btn = document.getElementById(btnId);
            if (!btn) return;
            const textSpan = btn.querySelector('span:not(.material-icons-round)');
            const spinner = btn.querySelector('.animate-spin');
            btn.disabled = isLoading;
            if (isLoading) {
                btn.classList.add('opacity-70', 'cursor-not-allowed');
            } else {
                btn.classList.remove('opacity-70', 'cursor-not-allowed');
            }
            if (textSpan) {
                textSpan.classList.toggle('opacity-0', isLoading);
            }
            if (spinner) {
                spinner.classList.toggle('hidden', !isLoading);
            }
        }

        function sendOtp() {
            const usernameInput = document.getElementById('fp-username').value.trim();
            const emailInput = document.getElementById('fp-email').value.trim();
            const errorElement = document.getElementById('fp-error-1');

            if (!usernameInput || usernameInput.length < 2) {
                errorElement.textContent = "Masukkan username akun Anda.";
                errorElement.classList.remove('hidden');
                return;
            }

            if (!emailInput || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(emailInput)) {
                errorElement.textContent = "Masukkan alamat email yang valid.";
                errorElement.classList.remove('hidden');
                return;
            }

            setButtonLoading('btn-send-otp', true);
            errorElement.classList.add('hidden');

            fetch('{{ url("api/forgot-password/send-otp") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ username: usernameInput, email: emailInput })
            })
            .then(parseForgotPasswordResponse)
            .then(data => {
                setButtonLoading('btn-send-otp', false);
                if (data.success) {
                    forgotPasswordUsername = usernameInput;
                    forgotPasswordEmail = emailInput;
                    document.getElementById('display-email').textContent = forgotPasswordEmail;
                    document.getElementById('fp-otp').value = '';
                    showStep(2);
                    setTimeout(() => document.getElementById('fp-otp').focus(), 100);
                } else {
                    errorElement.textContent = data.message || "Gagal mengirim OTP.";
                    errorElement.classList.remove('hidden');
                }
            })
            .catch(() => {
                setButtonLoading('btn-send-otp', false);
                errorElement.textContent = "Terjadi kesalahan koneksi jaringan.";
                errorElement.classList.remove('hidden');
            });
        }

        function verifyOtp() {
            const fpOtpEl = document.getElementById('fp-otp');
            const otpInput = normalizeForgotOtp(fpOtpEl.value);
            fpOtpEl.value = otpInput;
            const errorElement = document.getElementById('fp-error-2');

            if (!/^\d{6}$/.test(otpInput)) {
                errorElement.textContent = "Kode OTP harus persis 6 digit angka.";
                errorElement.classList.remove('hidden');
                return;
            }

            errorElement.classList.add('hidden');
            showStep(3);
            setTimeout(() => document.getElementById('fp-password').focus(), 100);
        }

        function resetPassword() {
            const password = document.getElementById('fp-password').value;
            const confirm = document.getElementById('fp-password-confirm').value;
            const fpOtpEl = document.getElementById('fp-otp');
            const otp = normalizeForgotOtp(fpOtpEl.value);
            fpOtpEl.value = otp;
            const errorElement = document.getElementById('fp-error-3');

            if (!forgotPasswordEmail || !forgotPasswordUsername) {
                errorElement.textContent = "Sesi pemulihan tidak valid. Mulai lagi dari kirim OTP.";
                errorElement.classList.remove('hidden');
                showStep(1);
                return;
            }

            if (password.length < 8) {
                errorElement.textContent = "Password minimal harus 8 karakter.";
                errorElement.classList.remove('hidden');
                return;
            }
            if (password !== confirm) {
                errorElement.textContent = "Konfirmasi password tidak cocok dengan password baru.";
                errorElement.classList.remove('hidden');
                return;
            }

            setButtonLoading('btn-reset-password', true);
            errorElement.classList.add('hidden');

            fetch('{{ url("api/forgot-password/reset") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    username: forgotPasswordUsername,
                    email: forgotPasswordEmail,
                    otp: otp,
                    password: password
                })
            })
            .then(parseForgotPasswordResponse)
            .then(data => {
                setButtonLoading('btn-reset-password', false);
                if (data.success) {
                    closeForgotPasswordModal();
                    Swal.fire({
                        icon: 'success',
                        title: 'Pemulihan Berhasil',
                        text: 'Password Anda berhasil diperbarui. Silakan login menggunakan password baru Anda.',
                        confirmButtonColor: '#10b981',
                        background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                        customClass: {
                            popup: 'rounded-3xl',
                            confirmButton: 'rounded-xl px-6 py-2 font-bold uppercase tracking-wide'
                        }
                    }).then(() => {
                        window.location.reload();
                    });
                } else {
                    errorElement.textContent = data.message || "Gagal mereset password.";
                    errorElement.classList.remove('hidden');
                    const msg = (data.message || '').toLowerCase();
                    if (msg.includes('otp') || msg.includes('kode')) {
                        setTimeout(() => {
                            showStep(2);
                            const err2 = document.getElementById('fp-error-2');
                            err2.textContent = data.message || 'Periksa kembali kode OTP.';
                            err2.classList.remove('hidden');
                        }, 800);
                    }
                }
            })
            .catch(() => {
                setButtonLoading('btn-reset-password', false);
                errorElement.textContent = "Terjadi kesalahan koneksi jaringan.";
                errorElement.classList.remove('hidden');
            });
        }

    </script>
@endsection