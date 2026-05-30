@extends('template.masteru')

@section('page_title', 'Profil Pengguna ✨')
@section('page_subtitle', 'Ringkasan aktivitas dan performa keuangan Anda')

@section('content')
  <div id="page-skeleton">
    @include('user.skeletons.profile')
  </div>

  <div id="main-content" class="max-w-6xl mx-auto space-y-8 hidden">
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
      {{-- User Card --}}
      <div class="lg:col-span-1 space-y-8">
        <div
          class="bg-surface-light dark:bg-surface-dark rounded-[40px] p-8 shadow-card border border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-primary-50 dark:bg-primary-900/10 flex items-center justify-center text-primary-500">
              <span class="material-icons-round">manage_accounts</span>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Keamanan & Akun</p>
              <h4 class="text-lg font-black text-slate-800 dark:text-white mt-1">Pengaturan Akun</h4>
            </div>
          </div>

          <button type="button" id="security-toggle"
            class="w-full flex items-center justify-between px-4 py-4 rounded-2xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 hover:bg-slate-100/70 dark:hover:bg-white/10 transition-all">
            <div class="min-w-0 text-left">
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Gmail verifikasi</p>
              <p class="mt-1 text-sm font-black text-slate-800 dark:text-white truncate">
                {{ Auth::user()->email ?: 'Belum diisi' }}
              </p>
            </div>
            <span id="security-toggle-icon" class="material-icons-round text-slate-400">expand_more</span>
          </button>

          <form id="security-form" action="{{ route('user.update_email') }}" method="POST" class="space-y-6 hidden mt-6">
            @csrf
            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Gmail (Verifikasi)</label>
              <div class="relative group mt-2">
                <span
                  class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">email</span>
                <input type="email" name="email" id="profile-email-input"
                  value="{{ old('email', Auth::user()->email) }}"
                  placeholder="nama@gmail.com"
                  autocomplete="email"
                  spellcheck="false"
                  inputmode="email"
                  class="pointer-events-auto w-full cursor-text pl-14 pr-6 py-5 bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white @error('email') ring-2 ring-rose-500 @enderror">
              </div>
              @error('email')
                <p class="mt-2 text-[10px] font-bold text-rose-500 ml-2">{{ $message }}</p>
              @enderror
              <p class="mt-2 text-[10px] text-slate-500 ml-2 italic">* Optional: Digunakan untuk verifikasi dan pemulihan akun.</p>
            </div>

            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Password</label>
              <div class="relative group mt-2">
                <span
                  class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">lock</span>
                <input type="password" name="password" id="profile-email-password"
                  placeholder="Masukkan password untuk konfirmasi"
                  autocomplete="current-password"
                  class="w-full pl-14 pr-14 py-5 bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-sm text-slate-800 dark:text-white @error('password') ring-2 ring-rose-500 @enderror">
                <button type="button" id="toggle-profile-email-password"
                  class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-300 hover:text-primary-500 transition-colors">
                  <span class="material-icons-round text-lg">visibility</span>
                </button>
              </div>
              @error('password')
                <p class="mt-2 text-[10px] font-bold text-rose-500 ml-2">{{ $message }}</p>
              @enderror
            </div>

            <button type="submit"
              class="w-full py-4 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl font-black text-sm transition-all active:scale-95">
              Simpan Perubahan
            </button>
          </form>
        </div>

        <div
          class="bg-surface-light dark:bg-surface-dark rounded-[40px] p-10 text-center shadow-card border border-slate-100 dark:border-slate-800">
          
          {{-- Interactive Avatar --}}
          <div class="relative inline-block mb-6 group cursor-pointer" onclick="openAvatarModal()">
            <div
              class="w-28 h-28 mx-auto rounded-[36px] bg-gradient-to-br from-primary-400 to-secondary-400 p-[3px] shadow-lg shadow-primary-500/20 transition-all duration-500 group-hover:scale-105 group-hover:rotate-3">
              <div class="w-full h-full rounded-[33px] bg-white dark:bg-surface-dark flex items-center justify-center overflow-hidden">
                @if(Auth::user()->avatar)
                  <img src="{{ Auth::user()->avatar }}" class="w-full h-full object-cover" id="main-avatar-display" alt="Avatar">
                @else
                  <span id="main-avatar-initials"
                    class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-br from-primary-600 to-secondary-600">
                    {{ strtoupper(substr(Auth::user()->username, 0, 1)) }}
                  </span>
                @endif
              </div>
            </div>
            <div class="absolute -bottom-2 -right-2 w-10 h-10 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl shadow-xl flex items-center justify-center border-4 border-white dark:border-surface-dark group-hover:scale-110 transition-transform duration-300">
              <span class="material-icons-round text-lg">camera_alt</span>
            </div>
          </div>

          <h3 class="text-2xl font-black text-slate-800 dark:text-white tracking-tight mb-1">
            {{ Auth::user()->username }}
          </h3>
          <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Anggota sejak
            {{ Auth::user()->created_at?->format('F Y') }}
          </p>

          <div class="mt-10 pt-10 border-t border-slate-50 dark:border-slate-800/50 space-y-6 text-left">
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-2">Uang Saya</p>
              <p class="text-3xl font-black text-primary-600 dark:text-primary-400 tracking-tighter">Rp
                <span class="rt-balance">{{ number_format($saldoNow ?? 0, 0, ',', '.') }}</span>
              </p>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Uang Masuk</p>
                <p class="text-sm font-bold text-green-500">Rp <span class="rt-monthly-pemasukan">{{ number_format($pemasukanBulan ?? 0, 0, ',', '.') }}</span></p>
              </div>
              <div class="p-4 bg-slate-50 dark:bg-white/5 rounded-2xl">
                <p class="text-[9px] font-black text-slate-400 uppercase tracking-widest mb-1">Uang Keluar</p>
                <p class="text-sm font-bold text-red-500">Rp <span class="rt-monthly-pengeluaran">{{ number_format($pengeluaranBulan ?? 0, 0, ',', '.') }}</span></p>
              </div>
            </div>
          </div>

          <div class="mt-8 pt-8 border-t border-slate-50 dark:border-slate-800/50 space-y-3">
            <button onclick="confirmResetLocal()"
              class="w-full flex items-center justify-center px-6 py-4 bg-amber-50 dark:bg-amber-900/10 text-amber-600 font-bold rounded-2xl hover:bg-amber-500 hover:text-white transition-all group duration-300">
              <span
                class="material-icons-round mr-3 transition-transform group-hover:rotate-180 duration-500">refresh</span>
              Hapus Semua Catatan
            </button>
          </div>

          <form id="reset-form-local" action="{{ route('resetSaldo') }}" method="POST" class="hidden">
            @csrf
            <input type="hidden" name="password" id="reset-password-input-local">
          </form>
        </div>

        {{-- Preferensi Keamanan & Mata Uang --}}
        <div
          class="bg-surface-light dark:bg-surface-dark rounded-[40px] p-8 shadow-card border border-slate-100 dark:border-slate-800">
          <div class="flex items-center gap-4 mb-6">
            <div class="w-12 h-12 rounded-2xl bg-indigo-50 dark:bg-indigo-900/10 flex items-center justify-center text-indigo-500">
              <span class="material-icons-round">settings</span>
            </div>
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Preferensi Keuangan</p>
              <h4 class="text-lg font-black text-slate-800 dark:text-white mt-1">Keamanan & Mata Uang</h4>
            </div>
          </div>

          {{-- Ganti Password Form --}}
          <div class="border-b border-slate-50 dark:border-slate-800/50 pb-6 mb-6">
            <h5 class="text-xs font-black text-slate-700 dark:text-slate-200 mb-4 uppercase tracking-wider">Ganti Password</h5>
            <form action="{{ route('user.update_password') }}" method="POST" class="space-y-4">
              @csrf
              <div>
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Password Saat Ini</label>
                <div class="relative group mt-1">
                  <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors">lock_open</span>
                  <input type="password" name="current_password" required
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-xs text-slate-800 dark:text-white">
                </div>
              </div>
              <div>
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Password Baru</label>
                <div class="relative group mt-1">
                  <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors">lock</span>
                  <input type="password" name="new_password" required
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-xs text-slate-800 dark:text-white">
                </div>
              </div>
              <div>
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Konfirmasi Password Baru</label>
                <div class="relative group mt-1">
                  <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors">enhanced_encryption</span>
                  <input type="password" name="new_password_confirmation" required
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-xs text-slate-800 dark:text-white">
                </div>
              </div>
              <button type="submit" class="w-full py-3.5 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl font-black text-xs transition-all active:scale-95">
                Update Password
              </button>
            </form>
          </div>

          {{-- Preferensi Mata Uang --}}
          <div>
            <h5 class="text-xs font-black text-slate-700 dark:text-slate-200 mb-4 uppercase tracking-wider">Mata Uang & Format</h5>
            <form action="{{ route('user.update_currency') }}" method="POST" class="space-y-4">
              @csrf
              <div>
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Mata Uang Utama</label>
                <div class="relative group mt-1">
                  <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors">payments</span>
                  <select name="currency" required
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-xs text-slate-800 dark:text-white appearance-none">
                    <option value="IDR" {{ Auth::user()->currency == 'IDR' ? 'selected' : '' }}>IDR (Rupiah)</option>
                    <option value="USD" {{ Auth::user()->currency == 'USD' ? 'selected' : '' }}>USD (Dollar)</option>
                    <option value="MYR" {{ Auth::user()->currency == 'MYR' ? 'selected' : '' }}>MYR (Ringgit)</option>
                  </select>
                </div>
              </div>
              <div>
                <label class="text-[9px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Format Tampilan</label>
                <div class="relative group mt-1">
                  <span class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-indigo-500 transition-colors">visibility</span>
                  <select name="currency_format" required
                    class="w-full pl-12 pr-4 py-3.5 bg-slate-50 dark:bg-white/5 border-none rounded-2xl focus:ring-2 focus:ring-primary-500/50 transition-all font-bold text-xs text-slate-800 dark:text-white appearance-none">
                    <option value="standard" {{ Auth::user()->currency_format == 'standard' ? 'selected' : '' }}>Standar (e.g. 10.000.000)</option>
                    <option value="compact" {{ Auth::user()->currency_format == 'compact' ? 'selected' : '' }}>Ringkas (e.g. 10Jt / 10M)</option>
                  </select>
                </div>
              </div>
              <button type="submit" class="w-full py-3.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-2xl font-black text-xs transition-all active:scale-95 shadow-lg shadow-indigo-600/20">
                Simpan Preferensi
              </button>
            </form>
          </div>
        </div>

        <div
          class="bg-surface-light dark:bg-surface-dark rounded-[40px] p-8 shadow-card border border-slate-100 dark:border-slate-800">
          <div class="flex items-center justify-between gap-4 mb-6">
            <div>
              <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Reminder Otomatis</p>
              <h4 class="text-lg font-black text-slate-800 dark:text-white mt-2">Pengingat finansial</h4>
            </div>
            <span class="material-icons-round text-primary-500">notifications_active</span>
          </div>

          <form action="{{ route('user.reminder_preferences') }}" method="POST" class="space-y-5">
            @csrf

            <label class="flex items-start gap-3 p-4 rounded-2xl bg-slate-50 dark:bg-white/5">
              <input type="checkbox" name="reminders_enabled" value="1" class="mt-1 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                {{ old('reminders_enabled', $notificationPreference->reminders_enabled ?? true) ? 'checked' : '' }}>
              <span>
                <span class="block text-sm font-bold text-slate-800 dark:text-white">Aktifkan semua reminder</span>
                <span class="block text-xs text-slate-500 mt-1">Matikan opsi ini jika Anda ingin menghentikan semua pengingat otomatis.</span>
              </span>
            </label>

            <div class="grid grid-cols-1 gap-4">
              <label class="flex items-start gap-3">
                <input type="checkbox" name="daily_reminder_enabled" value="1" class="mt-1 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                  {{ old('daily_reminder_enabled', $notificationPreference->daily_reminder_enabled ?? true) ? 'checked' : '' }}>
                <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Reminder harian jika belum ada transaksi</span>
              </label>
              <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Jam reminder harian</label>
                <input type="number" min="0" max="23" name="daily_reminder_hour"
                  value="{{ old('daily_reminder_hour', $notificationPreference->daily_reminder_hour ?? 20) }}"
                  class="mt-2 w-full rounded-2xl border-0 bg-slate-50 dark:bg-white/5 px-5 py-4 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/50">
              </div>

              <label class="flex items-start gap-3">
                <input type="checkbox" name="budget_alert_enabled" value="1" class="mt-1 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                  {{ old('budget_alert_enabled', $notificationPreference->budget_alert_enabled ?? true) ? 'checked' : '' }}>
                <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Reminder saat budget kategori menipis</span>
              </label>
              <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Ambang budget (%)</label>
                <input type="number" min="50" max="100" name="budget_alert_threshold"
                  value="{{ old('budget_alert_threshold', $notificationPreference->budget_alert_threshold ?? 80) }}"
                  class="mt-2 w-full rounded-2xl border-0 bg-slate-50 dark:bg-white/5 px-5 py-4 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/50">
              </div>

              <label class="flex items-start gap-3">
                <input type="checkbox" name="dream_reminder_enabled" value="1" class="mt-1 rounded border-slate-300 text-primary-600 focus:ring-primary-500"
                  {{ old('dream_reminder_enabled', $notificationPreference->dream_reminder_enabled ?? true) ? 'checked' : '' }}>
                <span class="text-sm font-medium text-slate-600 dark:text-slate-300">Reminder impian yang lama tidak disetor</span>
              </label>
              <div>
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Jeda tanpa setoran (hari)</label>
                <input type="number" min="1" max="30" name="dream_inactive_days"
                  value="{{ old('dream_inactive_days', $notificationPreference->dream_inactive_days ?? 7) }}"
                  class="mt-2 w-full rounded-2xl border-0 bg-slate-50 dark:bg-white/5 px-5 py-4 text-sm font-bold text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/50">
              </div>
            </div>

            @if($errors->any())
              <div class="rounded-2xl bg-red-50 text-red-600 px-4 py-3 text-sm font-medium">
                {{ $errors->first() }}
              </div>
            @endif

            <button type="submit"
              class="w-full flex items-center justify-center px-6 py-4 rounded-2xl bg-primary-600 text-white font-bold hover:bg-primary-700 transition-all">
              Simpan Preferensi Reminder
            </button>
          </form>
        </div>
      </div>

      {{-- Chart & Table --}}
      <div class="lg:col-span-2 space-y-8">
        <div
          class="bg-surface-light dark:bg-surface-dark rounded-[40px] p-10 shadow-card border border-slate-100 dark:border-slate-800">
          <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-8 flex items-center gap-3">
            <span class="material-icons-round text-primary-500">analytics</span> Analisis 6 Bulan Terakhir
          </h3>
          <div class="h-[300px]">
            <canvas id="profileFinanceChart"></canvas>
          </div>
        </div>

        <div
          class="bg-surface-light dark:bg-surface-dark rounded-[40px] shadow-card border border-slate-100 dark:border-slate-800 overflow-hidden">
          <div class="p-8 border-b border-slate-50 dark:border-slate-800 flex items-center justify-between">
            <h4 class="text-xs font-black text-slate-400 uppercase tracking-widest">Ringkasan Bulanan</h4>
            <span class="material-icons-round text-slate-300">summarize</span>
          </div>
          <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
              <thead>
                <tr class="bg-slate-50/50 dark:bg-white/5">
                  <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest">Bulan</th>
                  <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                    Uang Masuk</th>
                  <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">
                    Uang Keluar</th>
                  <th class="px-8 py-4 text-[10px] font-black text-slate-400 uppercase tracking-widest text-right">Sisa
                    Uang
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-slate-50 dark:divide-slate-800/50">
                @foreach($monthlySummary ?? [] as $row)
                  <tr class="hover:bg-slate-50/50 dark:hover:bg-white/5 transition-colors group">
                    <td
                      class="px-8 py-5 text-sm font-bold text-slate-700 dark:text-slate-200 group-hover:text-primary-500 transition-colors">
                      {{ $row['label'] ?? '-' }}
                    </td>
                    <td class="px-8 py-5 text-sm font-bold text-green-500 text-right">
                      +{{ number_format($row['pemasukan'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-8 py-5 text-sm font-bold text-red-500 text-right">
                      -{{ number_format($row['pengeluaran'] ?? 0, 0, ',', '.') }}
                    </td>
                    <td class="px-8 py-5 text-sm font-black text-slate-800 dark:text-white tracking-tight text-right">
                      Rp {{ number_format($row['saldo'] ?? 0, 0, ',', '.') }}
                    </td>
                  </tr>
                @endforeach
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>

    {{-- Feedback Section --}}
    <div class="max-w-6xl mx-auto mt-8">
      <div class="relative overflow-hidden bg-surface-light dark:bg-surface-dark rounded-[40px] shadow-card border border-slate-100 dark:border-slate-800">

        {{-- Decorative gradient header --}}
        <div class="relative px-8 pt-8 pb-6 border-b border-slate-100 dark:border-white/5">
          <div class="absolute inset-0 bg-gradient-to-r from-violet-500/5 via-indigo-500/5 to-transparent pointer-events-none rounded-t-[40px]"></div>
          <div class="relative flex items-center justify-between">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-violet-500 to-indigo-500 flex items-center justify-center text-white shadow-lg shadow-violet-500/30">
                <span class="material-icons-round">rate_review</span>
              </div>
              <div>
                <p class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Bantu Kami Berkembang</p>
                <h4 class="text-xl font-black text-slate-800 dark:text-white mt-0.5 tracking-tight">Kirim Masukan</h4>
              </div>
            </div>
            <span class="hidden sm:flex items-center gap-2 text-[10px] font-black text-violet-500 bg-violet-50 dark:bg-violet-500/10 px-3 py-1.5 rounded-full">
              <span class="w-1.5 h-1.5 rounded-full bg-violet-500 animate-pulse"></span>
              Anonim & Aman
            </span>
          </div>
        </div>

        <div class="p-8">
          <form id="feedback-form" action="{{ route('user.send_feedback') }}" method="POST" class="space-y-7">
            @csrf

            {{-- Kategori Feedback --}}
            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1">Jenis Masukan</label>
              <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mt-3">
                @php
                  $categories = [
                    ['value' => 'Saran Fitur',  'icon' => 'lightbulb',       'color' => 'amber'],
                    ['value' => 'Bug Report',   'icon' => 'bug_report',      'color' => 'rose'],
                    ['value' => 'Pertanyaan',   'icon' => 'help_outline',    'color' => 'sky'],
                    ['value' => 'Lainnya',      'icon' => 'more_horiz',      'color' => 'slate'],
                  ];
                  $colorMap = [
                    'amber' => ['bg' => 'bg-amber-50 dark:bg-amber-500/10',  'border' => 'border-amber-400',  'text' => 'text-amber-600 dark:text-amber-400',  'ring' => 'ring-amber-400/40'],
                    'rose'  => ['bg' => 'bg-rose-50 dark:bg-rose-500/10',    'border' => 'border-rose-400',   'text' => 'text-rose-600 dark:text-rose-400',    'ring' => 'ring-rose-400/40'],
                    'sky'   => ['bg' => 'bg-sky-50 dark:bg-sky-500/10',      'border' => 'border-sky-400',    'text' => 'text-sky-600 dark:text-sky-400',      'ring' => 'ring-sky-400/40'],
                    'slate' => ['bg' => 'bg-slate-50 dark:bg-white/5',       'border' => 'border-slate-400',  'text' => 'text-slate-600 dark:text-slate-300',  'ring' => 'ring-slate-400/40'],
                  ];
                @endphp

                @foreach($categories as $cat)
                  @php $c = $colorMap[$cat['color']]; @endphp
                  <button type="button"
                    data-category="{{ $cat['value'] }}"
                    class="category-btn group relative flex flex-col items-center gap-2 px-4 py-4 rounded-2xl border-2 border-transparent bg-slate-50 dark:bg-white/5 hover:{{ $c['bg'] }} hover:border-{{ $cat['color'] }}-300 dark:hover:border-{{ $cat['color'] }}-500/50 transition-all duration-200">
                    <span class="material-icons-round text-2xl text-slate-400 group-hover:{{ $c['text'] }} transition-colors">{{ $cat['icon'] }}</span>
                    <span class="text-[11px] font-black text-slate-500 dark:text-slate-400 group-hover:{{ $c['text'] }} transition-colors tracking-wide">{{ $cat['value'] }}</span>
                  </button>
                @endforeach
              </div>
              <input type="hidden" name="kategori_feedback" id="kategori-input" value="{{ old('kategori_feedback', '') }}">
            </div>

            {{-- Subjek (Opsional) --}}
            <div>
              <div class="flex items-center justify-between ml-1 mb-2">
                <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em]">Subjek</label>
                <span class="text-[10px] font-bold text-slate-400 bg-slate-100 dark:bg-white/5 px-2 py-0.5 rounded-full">Opsional</span>
              </div>
              <div class="relative group">
                <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-violet-500 transition-colors text-[20px]">title</span>
                <input type="text" name="subjek" id="subjek-input" maxlength="100"
                  placeholder="Judul singkat masukan Anda..."
                  value="{{ old('subjek') }}"
                  class="w-full pl-14 pr-6 py-4 bg-slate-50 dark:bg-white/5 border-2 border-transparent focus:border-violet-400/50 rounded-2xl focus:ring-0 transition-all font-semibold text-sm text-slate-800 dark:text-white placeholder-slate-400">
              </div>
            </div>

            {{-- Pesan --}}
            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1 mb-2 block">Pesan <span class="text-rose-400">*</span></label>
              <div class="relative group">
                <textarea name="pesan" id="pesan-input" required rows="4" maxlength="1000"
                  placeholder="Ceritakan pengalaman, saran, atau masalah yang Anda temui secara detail..."
                  class="w-full px-6 py-4 bg-slate-50 dark:bg-white/5 border-2 border-transparent focus:border-violet-400/50 rounded-2xl focus:ring-0 transition-all font-medium text-sm text-slate-800 dark:text-white resize-none placeholder-slate-400">{{ old('pesan') }}</textarea>
                <span id="char-count" class="absolute bottom-3 right-4 text-[10px] font-bold text-slate-400">0 / 1000</span>
              </div>
            </div>

            {{-- Rating --}}
            <div>
              <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-1 mb-3 block">Rating Pengalaman</label>
              <div class="flex items-center gap-2" id="star-rating">
                @for($i = 1; $i <= 5; $i++)
                  <button type="button" data-value="{{ $i }}"
                    class="star-btn relative w-11 h-11 rounded-xl flex items-center justify-center transition-all duration-150 hover:scale-110 bg-slate-100 dark:bg-white/5 text-slate-300">
                    <span class="material-icons-round text-2xl">star</span>
                  </button>
                @endfor
                <div class="ml-3 flex flex-col">
                  <span id="rating-label" class="text-sm font-black text-slate-400">—</span>
                  <span id="rating-sublabel" class="text-[10px] text-slate-400 mt-0.5">Pilih bintang di atas</span>
                </div>
              </div>
              <input type="hidden" name="rating" id="rating-input" value="">
            </div>

            {{-- Submit --}}
            <div class="pt-2">
              <button type="submit" id="feedback-submit-btn"
                class="w-full py-4 bg-gradient-to-r from-violet-500 to-indigo-500 hover:from-violet-600 hover:to-indigo-600 text-white rounded-2xl font-black text-sm shadow-lg shadow-violet-500/20 transition-all active:scale-[0.98] flex items-center justify-center gap-2 group">
                <span class="material-icons-round text-base group-hover:translate-x-0.5 transition-transform">send</span>
                Kirim Masukan
              </button>
              <p class="text-center text-[10px] text-slate-400 mt-3">Masukan Anda akan langsung diterima oleh tim KasSaku</p>
            </div>
          </form>
        </div>
      </div>
    </div>

  </div>
@endsection

@section('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      var sk = document.getElementById('page-skeleton');
      var mc = document.getElementById('main-content');
      if (sk) sk.classList.add('hidden');
      if (mc) {
        mc.classList.remove('hidden');
        mc.classList.add('animate-fade-in');
      }
    });

    // Avatar Logic
    function openAvatarModal() {
        Swal.fire({
            title: 'Ganti Avatar ✨',
            text: 'Pilih bagaimana Anda ingin tampil di KasSaku',
            background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
            showConfirmButton: false,
            showCloseButton: true,
            html: `
                <div class="space-y-6 pt-4">
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="triggerAvatarUpload()" class="p-6 rounded-3xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 hover:border-primary-500 transition-all group">
                            <span class="material-icons-round text-3xl text-primary-500 mb-2 group-hover:scale-110 transition-transform">cloud_upload</span>
                            <span class="block text-xs font-black uppercase tracking-widest text-slate-800 dark:text-white">Upload Foto</span>
                        </button>
                        <button onclick="setPredefinedAvatarModal()" class="p-6 rounded-3xl bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/10 hover:border-primary-500 transition-all group">
                            <span class="material-icons-round text-3xl text-secondary-500 mb-2 group-hover:scale-110 transition-transform">face</span>
                            <span class="block text-xs font-black uppercase tracking-widest text-slate-800 dark:text-white">Pilih Avatar</span>
                        </button>
                    </div>
                    <button onclick="removeAvatar()" class="w-full py-4 text-xs font-black text-rose-500 uppercase tracking-widest hover:bg-rose-50 dark:hover:bg-rose-500/10 rounded-2xl transition-all">
                        Hapus Avatar & Gunakan Inisial
                    </button>
                </div>
                <input type="file" id="avatar-file-input" class="hidden" accept="image/*" onchange="handleAvatarFileSelect(this)">
            `
        });
    }

    function triggerAvatarUpload() {
        document.getElementById('avatar-file-input').click();
    }

    async function handleAvatarFileSelect(input) {
        if (!input.files || !input.files[0]) return;
        
        const file = input.files[0];
        const formData = new FormData();
        formData.append('avatar', file);
        formData.append('_token', "{{ csrf_token() }}");

        Swal.showLoading();

        try {
            const response = await fetch("{{ route('user.avatar.upload') }}", {
                method: 'POST',
                body: formData
            });
            const result = await response.json();
            
            if (result.success) {
                location.reload();
            } else {
                Swal.fire('Gagal!', result.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
        }
    }

    function setPredefinedAvatarModal() {
        const avatars = [
            { id: 'avatar-1.png', url: "{{ asset('assets/avatars/avatar-1.png') }}" },
            { id: 'avatar-2.png', url: "{{ asset('assets/avatars/avatar-2.png') }}" },
            { id: 'avatar-3.png', url: "{{ asset('assets/avatars/avatar-3.png') }}" },
        ];

        Swal.fire({
            title: 'Pilih Avatar Karakter',
            background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
            showConfirmButton: false,
            showCloseButton: true,
            width: '32rem',
            html: `
                <div class="grid grid-cols-2 gap-6 p-4">
                    ${avatars.map(av => `
                        <button onclick="applyPredefinedAvatar('${av.id}')" class="group relative aspect-square rounded-[32px] overflow-hidden border-4 border-transparent hover:border-primary-500 transition-all shadow-lg hover:shadow-primary-500/20">
                            <img src="${av.url}" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                            <div class="absolute inset-0 bg-primary-500/20 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </button>
                    `).join('')}
                </div>
            `
        });
    }

    async function applyPredefinedAvatar(avatarId) {
        Swal.showLoading();
        try {
            const response = await fetch("{{ route('user.avatar.predefined') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                },
                body: JSON.stringify({ avatar_id: avatarId })
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            } else {
                Swal.fire('Gagal!', result.message, 'error');
            }
        } catch (error) {
            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
        }
    }

    async function removeAvatar() {
        Swal.showLoading();
        try {
            const response = await fetch("{{ route('user.avatar.remove') }}", {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': "{{ csrf_token() }}"
                }
            });
            const result = await response.json();
            if (result.success) {
                location.reload();
            }
        } catch (error) {
            Swal.fire('Error!', 'Terjadi kesalahan sistem.', 'error');
        }
    }

    function confirmResetLocal() {
      Swal.fire({
        title: 'Verifikasi Password',
        text: 'Password diperlukan untuk menghapus riwayat transaksi dan setoran tabungan impian pada bulan ini.',
        icon: 'warning',
        html: `
                <div class="space-y-4 pt-4 text-left">
                    <p class="text-xs font-medium text-slate-500 dark:text-slate-300 leading-relaxed">
                        Reset ini hanya berlaku untuk bulan berjalan. Transaksi dan setoran tabungan impian pada bulan ini akan dihapus, sedangkan bulan lain tetap aman.
                    </p>
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Password Anda</label>
                    <div class="relative group">
                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">lock</span>
                        <input type="password" id="swal-input-password" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-5 !pl-14 !pr-6 !font-bold !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all text-sm" placeholder="••••••••">
                    </div>
                </div>
            `,
        showCancelButton: true,
        confirmButtonColor: '#d33',
        cancelButtonColor: '#64748b',
        confirmButtonText: 'Ya, Reset!',
        cancelButtonText: 'Batal',
        background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
        color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
        preConfirm: () => {
          const password = document.getElementById('swal-input-password').value;
          if (!password) {
            Swal.showValidationMessage('Password wajib diisi!');
          }
          return password;
        }
      }).then((result) => {
        if (result.isConfirmed) {
          document.getElementById('reset-password-input-local').value = result.value;
          document.getElementById('reset-form-local').submit();
        }
      })
    }

    document.addEventListener('DOMContentLoaded', function () {
      const labels = {!! json_encode($profileLabels ?? []) !!};
      const pemasukan = {!! json_encode($profilePemasukan ?? []) !!};
      const pengeluaran = {!! json_encode($profilePengeluaran ?? []) !!};

      const ctx = document.getElementById('profileFinanceChart');
      if (ctx) {
        new Chart(ctx, {
          type: 'line',
          data: {
            labels: labels,
            datasets: [
              { label: 'Uang Masuk', data: pemasukan, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.05)', borderWidth: 4, tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 6 },
              { label: 'Uang Keluar', data: pengeluaran, borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.02)', borderWidth: 4, tension: 0.4, fill: true, pointRadius: 4, pointHoverRadius: 6 }
            ]
          },
          options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: { cornerRadius: 15, padding: 15 } },
            scales: {
              x: { grid: { display: false }, ticks: { font: { weight: 'bold', size: 10 }, color: '#94a3b8' } },
              y: { grid: { color: 'rgba(226, 232, 240, 0.4)', drawBorder: false }, ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID'), font: { weight: 'bold', size: 10 }, color: '#94a3b8' } }
            }
          }
        });
      }

      @if(session('success'))
        Swal.fire({ icon: 'success', title: 'Berhasil!', text: @json(session('success')), timer: 3000, showConfirmButton: false, background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff', color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d' });
      @endif
      @if(session('error'))
        Swal.fire({ icon: 'error', title: 'Gagal!', text: @json(session('error')), background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff', color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d' });
      @endif

      // --- Star Rating Logic ---
      const starBtns = document.querySelectorAll('.star-btn');
      const ratingInput = document.getElementById('rating-input');
      const ratingLabel = document.getElementById('rating-label');
      const ratingSubLabel = document.getElementById('rating-sublabel');
      const ratingData = [
        { label: '—',            sub: 'Pilih bintang di atas',            color: 'text-slate-400' },
        { label: 'Sangat Buruk', sub: 'Kami akan segera perbaiki',        color: 'text-rose-500' },
        { label: 'Buruk',        sub: 'Terima kasih atas masukannya',     color: 'text-orange-500' },
        { label: 'Cukup',        sub: 'Masih banyak ruang untuk tumbuh',  color: 'text-amber-500' },
        { label: 'Bagus',        sub: 'Senang mendengarnya!',             color: 'text-lime-500' },
        { label: 'Sangat Bagus', sub: 'Terima kasih! 🎉',                 color: 'text-emerald-500' },
      ];

      function updateStars(value) {
        starBtns.forEach(btn => {
          const v = parseInt(btn.dataset.value);
          if (v <= value) {
            btn.classList.remove('text-slate-300', 'bg-slate-100');
            btn.classList.add('text-amber-400', 'bg-amber-50', 'scale-110');
          } else {
            btn.classList.remove('text-amber-400', 'bg-amber-50', 'scale-110');
            btn.classList.add('text-slate-300', 'bg-slate-100');
          }
        });
        const d = ratingData[value] || ratingData[0];
        if (ratingLabel) { ratingLabel.textContent = d.label; ratingLabel.className = 'text-sm font-black transition-colors ' + d.color; }
        if (ratingSubLabel) ratingSubLabel.textContent = d.sub;
        ratingInput.value = value || '';
      }

      let selectedRating = 0;
      starBtns.forEach(btn => {
        btn.addEventListener('mouseenter', () => updateStars(parseInt(btn.dataset.value)));
        btn.addEventListener('mouseleave', () => updateStars(selectedRating));
        btn.addEventListener('click', () => {
          selectedRating = parseInt(btn.dataset.value);
          ratingInput.value = selectedRating;
          updateStars(selectedRating);
        });
      });

      // --- Category Selection Logic ---
      const categoryBtns = document.querySelectorAll('.category-btn');
      const kategoriInput = document.getElementById('kategori-input');
      const subjekInput = document.getElementById('subjek-input');
      const pesanInputEl = document.getElementById('pesan-input');

      const activeCategoryClasses = {
        'Saran Fitur': ['border-amber-400', 'bg-amber-50', 'dark:bg-amber-500/10'],
        'Bug Report':  ['border-rose-400',  'bg-rose-50',  'dark:bg-rose-500/10'],
        'Pertanyaan':  ['border-sky-400',   'bg-sky-50',   'dark:bg-sky-500/10'],
        'Lainnya':     ['border-slate-400', 'bg-slate-100','dark:bg-white/10'],
      };
      const placeholderMap = {
        'Saran Fitur': 'Fitur apa yang ingin Anda lihat di KasSaku?',
        'Bug Report':  'Jelaskan bug yang Anda temui secara detail...',
        'Pertanyaan':  'Apa yang ingin Anda tanyakan?',
        'Lainnya':     'Ceritakan pengalaman atau masukan Anda...',
      };

      categoryBtns.forEach(btn => {
        btn.addEventListener('click', () => {
          const val = btn.dataset.category;
          const isActive = kategoriInput.value === val;

          // Reset semua tombol
          categoryBtns.forEach(b => {
            b.classList.remove('border-amber-400','border-rose-400','border-sky-400','border-slate-400',
              'bg-amber-50','bg-rose-50','bg-sky-50','bg-slate-100',
              'dark:bg-amber-500/10','dark:bg-rose-500/10','dark:bg-sky-500/10','dark:bg-white/10');
            b.classList.add('border-transparent');
          });

          if (!isActive) {
            kategoriInput.value = val;
            const classes = activeCategoryClasses[val] || [];
            btn.classList.remove('border-transparent');
            classes.forEach(cls => btn.classList.add(cls));
            if (pesanInputEl) pesanInputEl.placeholder = placeholderMap[val] || 'Ceritakan masukan Anda...';
            if (subjekInput && !subjekInput.value) subjekInput.value = val;
          } else {
            kategoriInput.value = '';
            if (subjekInput && subjekInput.value === val) subjekInput.value = '';
          }
        });
      });

      // --- Char Counter ---
      const charCount = document.getElementById('char-count');
      if (pesanInputEl && charCount) {
        pesanInputEl.addEventListener('input', () => {
          const len = pesanInputEl.value.length;
          charCount.textContent = len + ' / 1000';
          charCount.className = 'absolute bottom-3 right-4 text-[10px] font-bold transition-colors ' +
            (len > 900 ? 'text-rose-400' : len > 700 ? 'text-amber-400' : 'text-slate-400');
        });
      }

      const toggle = document.getElementById('security-toggle');
      const form = document.getElementById('security-form');
      const icon = document.getElementById('security-toggle-icon');
      const pw = document.getElementById('profile-email-password');
      const pwToggle = document.getElementById('toggle-profile-email-password');

      const forceOpen = {{ ($errors->has('email') || $errors->has('password')) ? 'true' : 'false' }};
      const setOpen = (open) => {
        if (!form || !icon) return;
        form.classList.toggle('hidden', !open);
        icon.textContent = open ? 'expand_less' : 'expand_more';
      };
      setOpen(forceOpen);

      toggle?.addEventListener('click', () => {
        const isHidden = form?.classList.contains('hidden');
        setOpen(Boolean(isHidden));
        if (isHidden) {
          setTimeout(() => document.getElementById('profile-email-input')?.focus(), 50);
        }
      });

      pwToggle?.addEventListener('click', () => {
        if (!pw) return;
        const type = pw.getAttribute('type') === 'password' ? 'text' : 'password';
        pw.setAttribute('type', type);
        const i = pwToggle.querySelector('.material-icons-round');
        if (i) i.textContent = type === 'password' ? 'visibility' : 'visibility_off';
      });
    });
  </script>
@endsection
