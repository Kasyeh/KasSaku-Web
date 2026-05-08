@extends('template.auth')

@section('title', 'Reset Password')

@section('content')
    <div class="flex flex-col items-center">
        <div class="w-24 h-24 rounded-full glass-card flex items-center justify-center mb-8 shadow-primary-500/20">
            <span class="material-icons-round text-primary-500 text-5xl">vpn_key</span>
        </div>

        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter mb-2">Reset Password</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">Masukkan kode OTP dan password baru Anda</p>
        </div>

        <div class="glass-card rounded-[40px] p-10 w-full max-w-md">
            @if (session('success'))
                <div class="mb-6 p-4 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl">
                    <p class="text-xs font-bold text-emerald-600 text-center">{{ session('success') }}</p>
                </div>
            @endif

            <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
                @csrf
                <input type="hidden" name="email" value="{{ $email }}">
                <input type="hidden" name="username" value="{{ old('username', $username ?? '') }}">
                
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Kode OTP (6 Digit)</label>
                    <div class="relative group">
                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">pin</span>
                        <input type="text" name="otp" required maxlength="6" placeholder="000000"
                            class="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-white/5 border-none focus:ring-primary-500 text-slate-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-black text-lg tracking-[0.5em] text-center @error('otp') ring-2 ring-rose-500 @enderror">
                    </div>
                    @error('otp')
                        <p class="text-xs font-bold text-rose-500 ml-4">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Password Baru</label>
                    <div class="relative group">
                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">lock</span>
                        <input type="password" name="password" required placeholder="••••••••"
                            class="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-white/5 border-none focus:ring-primary-500 text-slate-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-sm @error('password') ring-2 ring-rose-500 @enderror">
                    </div>
                    @error('password')
                        <p class="text-xs font-bold text-rose-500 ml-4">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Konfirmasi Password</label>
                    <div class="relative group">
                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">lock</span>
                        <input type="password" name="password_confirmation" required placeholder="••••••••"
                            class="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-white/5 border-none focus:ring-primary-500 text-slate-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-sm">
                    </div>
                </div>

                <button type="submit"
                    class="w-full py-4 bg-primary-500 hover:bg-primary-600 text-white rounded-full font-black text-lg shadow-xl shadow-primary-500/30 transition-all active:scale-95 group">
                    Reset Password
                    <span class="material-icons-round align-middle ml-2 group-hover:translate-x-1 transition-transform">check_circle</span>
                </button>
            </form>
        </div>

        <div class="mt-8 px-6 py-3 glass-card rounded-2xl">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                Sudah ingat password?
                <a href="{{ route('login') }}" class="font-black text-primary-600 ml-1">Login Sekarang</a>
            </p>
        </div>
    </div>
@endsection
