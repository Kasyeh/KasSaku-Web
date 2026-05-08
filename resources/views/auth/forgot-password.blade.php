@extends('template.auth')

@section('title', 'Lupa Password')

@section('content')
    <div class="flex flex-col items-center">
        <div class="w-24 h-24 rounded-full glass-card flex items-center justify-center mb-8 shadow-primary-500/20">
            <span class="material-icons-round text-primary-500 text-5xl">lock_reset</span>
        </div>

        <div class="text-center mb-10">
            <h1 class="text-4xl font-black text-slate-900 dark:text-white tracking-tighter mb-2">Lupa Password</h1>
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">OTP hanya dikirim jika username dan email cocok dengan akun Anda</p>
        </div>

        <div class="glass-card rounded-[40px] p-10 w-full max-w-md">
            <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
                @csrf
                
                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Username</label>
                    <div class="relative group">
                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">person</span>
                        <input type="text" name="username" value="{{ old('username') }}" required autofocus placeholder="Username akun Anda"
                            autocomplete="username"
                            class="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-white/5 border-none focus:ring-primary-500 text-slate-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-sm @error('username') ring-2 ring-rose-500 @enderror">
                    </div>
                    @error('username')
                        <p class="text-xs font-bold text-rose-500 ml-4">{{ $message }}</p>
                    @enderror
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Email Terdaftar</label>
                    <div class="relative group">
                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">email</span>
                        <input type="email" name="email" value="{{ old('email') }}" required placeholder="nama@gmail.com"
                            autocomplete="email"
                            class="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-white/5 border-none focus:ring-primary-500 text-slate-800 dark:text-white rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-sm @error('email') ring-2 ring-rose-500 @enderror">
                    </div>
                    @error('email')
                        <p class="text-xs font-bold text-rose-500 ml-4">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit"
                    class="w-full py-4 bg-primary-500 hover:bg-primary-600 text-white rounded-full font-black text-lg shadow-xl shadow-primary-500/30 transition-all active:scale-95 group">
                    Kirim OTP
                    <span class="material-icons-round align-middle ml-2 group-hover:translate-x-1 transition-transform">send</span>
                </button>
            </form>
        </div>

        <div class="mt-8 px-6 py-3 glass-card rounded-2xl">
            <p class="text-sm font-medium text-slate-500 dark:text-slate-400">
                Kembali ke
                <a href="{{ route('login') }}" class="font-black text-primary-600 ml-1">Halaman Login</a>
            </p>
        </div>
    </div>
@endsection
