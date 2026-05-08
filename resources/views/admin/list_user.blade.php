@extends('template.master')

@section('page_title', 'Manajemen Pengguna 👥')
@section('page_subtitle', 'Pantau aktivitas, uang tersedia, dan akses keamanan seluruh pengguna')

@section('content')
    <div class="space-y-10">
        <!-- Page Header Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <a href="{{ url('transaksi/cetak') }}" class="flex items-center gap-2 px-5 py-3 bg-white dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-2xl shadow-sm font-bold text-slate-700 dark:text-slate-200 hover:scale-105 active:scale-95 transition-all">
                    <span class="material-icons-round text-primary-500">picture_as_pdf</span>
                    <span class="text-sm">Unduh PDF Laporan</span>
                </a>
                <div class="px-5 py-3 bg-primary-500/10 border border-primary-500/20 rounded-2xl shadow-glow">
                    <span class="text-xs font-black text-primary-700 dark:text-primary-400 uppercase tracking-widest">Total: {{ $list_user->total() }} Pengguna</span>
                </div>
            </div>
        </div>

        <!-- Premium Table Card -->
        <div class="card-premium rounded-[2.5rem] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full align-middle">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-white/5 border-b border-slate-50 dark:border-white/5">
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">No.</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Identitas</th>
                            <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest">Status Sistem</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Uang Tersedia</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                        @forelse($list_user as $lu)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-all group">
                                <td class="px-8 py-6">
                                    <span class="text-xs font-black text-slate-300 dark:text-slate-700 group-hover:text-primary-500 transition-colors">#{{ $list_user->firstItem() + $loop->index }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-slate-100 to-slate-200 dark:from-white/5 dark:to-white/10 flex items-center justify-center text-slate-500 dark:text-slate-400 font-black text-sm group-hover:from-primary-500 group-hover:to-primary-600 group-hover:text-white transition-all shadow-sm">
                                            {{ strtoupper(substr($lu->username, 0, 1)) }}
                                        </div>
                                        <div>
                                            <div class="text-sm font-bold text-slate-800 dark:text-white">{{ $lu->username }}</div>
                                            <div class="text-[10px] font-medium text-slate-400">UID: {{ $lu->id_user }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    @if($lu->active == 1)
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-100 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500 animate-pulse"></span>
                                            <span class="text-[10px] font-black uppercase tracking-tight">Akses Aktif</span>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 rounded-full">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span class="text-[10px] font-black uppercase tracking-tight">Sistem Diblokir</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="text-sm font-black text-slate-800 dark:text-white rt-user-balance" data-user-id="{{ $lu->id_user }}">Rp {{ number_format($lu->saldo ?? 0, 0, ',', '.') }}</div>
                                    <div class="text-[10px] font-medium text-slate-400">Uang Masuk: Rp {{ number_format($lu->pemasukan ?? 0, 0, ',', '.') }}</div>
                                </td>
                                <td class="px-8 py-6 text-right">
                                    <div class="flex justify-end gap-2 pr-2">
                                        <a href="javascript:void(0)" 
                                            onclick="verifyBeforeRedirect('{{ $lu->id_user }}')"
                                            class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-primary-500 hover:bg-primary-500 hover:text-white hover:shadow-glow transition-all" title="Lihat Detail">
                                            <span class="material-icons-round text-lg">visibility</span>
                                        </a>

                                        @if($lu->active == 1)
                                            <form action="{{ route('admin.user.block', $lu->id_user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-amber-500 hover:bg-amber-500 hover:text-white hover:shadow-lg transition-all" title="Blokir Akses">
                                                    <span class="material-icons-round text-lg">block</span>
                                                </button>
                                            </form>
                                        @else
                                            <form action="{{ route('admin.user.unblock', $lu->id_user) }}" method="POST" class="inline">
                                                @csrf
                                                <button type="submit" class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-indigo-500 hover:bg-indigo-500 hover:text-white hover:shadow-lg transition-all" title="Buka Blokir">
                                                    <span class="material-icons-round text-lg">lock_open</span>
                                                </button>
                                            </form>
                                        @endif

                                        <button type="button" class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-rose-500 hover:bg-rose-500 hover:text-white hover:shadow-lg transition-all" 
                                            onclick="confirmDeleteUser('{{ $lu->id_user }}', '{{ $lu->username }}')" title="Hapus Pengguna">
                                            <span class="material-icons-round text-lg">delete_forever</span>
                                        </button>
                                        
                                        <form id="delete-user-form-{{ $lu->id_user }}"
                                            action="{{ route('admin.user.hapus', $lu->id_user) }}" method="POST" style="display:none;">
                                            @csrf
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-8 py-20 text-center">
                                    <span class="material-icons-round text-4xl text-slate-200 mb-4">search_off</span>
                                    <p class="text-xs font-black text-slate-300 uppercase tracking-widest">Data pengguna tidak ditemukan</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($list_user->hasPages())
                <div class="px-8 py-6 border-t border-slate-50 dark:border-white/5">
                    {{ $list_user->onEachSide(1)->links('partials.pagination') }}
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
<script type="module">
    import { initializeApp } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
    import { getDatabase, ref, onValue } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

    const firebaseConfig = {
      databaseURL: "https://kassaku-8beb0-default-rtdb.asia-southeast1.firebasedatabase.app",
    };

    const app = initializeApp(firebaseConfig);
    const db = getDatabase(app);

    // Listen to changes for each user in the list
    document.querySelectorAll('.rt-user-balance').forEach(el => {
        const userId = el.getAttribute('data-user-id');
        if (userId) {
            const balanceRef = ref(db, `users/${userId}/balance/saldo`);
            onValue(balanceRef, (snapshot) => {
                const newSaldo = snapshot.val();
                if (newSaldo !== null) {
                    const formatIDR = (val) => new Intl.NumberFormat('id-ID').format(val);
                    el.innerText = 'Rp ' + formatIDR(newSaldo);
                    
                    // Add a brief glow effect to show change
                    el.classList.add('text-primary-500');
                    setTimeout(() => el.classList.remove('text-primary-500'), 2000);
                }
            });
        }
    });
</script>

    <script>
        function verifyBeforeRedirect(userId) {
            Swal.fire({
                title: 'Verifikasi Password',
                text: 'Silakan masukkan password admin Anda untuk melihat detail user.',
                input: 'password',
                inputAttributes: {
                    autocapitalize: 'off',
                    autocorrect: 'off'
                },
                showCancelButton: true,
                confirmButtonText: 'Verifikasi',
                cancelButtonText: 'Batal',
                showLoaderOnConfirm: true,
                background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                preConfirm: (password) => {
                    return fetch('{{ route("admin.verify_password") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ password: password })
                    })
                    .then(response => {
                        if (!response.ok) {
                            return response.json().then(data => {
                                throw new Error(data.message || 'Konfirmasi password gagal');
                            });
                        }
                        return response.json();
                    })
                    .catch(error => {
                        Swal.showValidationMessage(`Error: ${error.message}`);
                    });
                },
                allowOutsideClick: () => !Swal.isLoading()
            }).then((result) => {
                if (result.isConfirmed && result.value.success) {
                    window.location.href = "{{ url('list_user') }}/" + userId;
                }
            });
        }

        function confirmDeleteUser(userId, username) {
            Swal.fire({
                title: 'Terminasi User?',
                text: "Menghapus " + username + " akan melenyapkan seluruh data finansial mereka secara permanen.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Ya, Lenyapkan',
                cancelButtonText: 'Batal',
                background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-user-form-' + userId).submit();
                }
            });
        }
    </script>
@endsection
