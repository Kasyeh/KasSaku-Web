@extends('template.masteru')

@section('page_title', 'Catatan Keuangan 🧶')
@section('page_subtitle', 'Pantau seluruh catatan arus kas Anda')

@section('content')
    <div class="max-w-6xl mx-auto space-y-8 animate-fade-in">
        {{-- Header Actions --}}
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="flex items-center gap-3">
                <button
                    type="button"
                    data-guide-open="riwayat"
                    class="flex items-center gap-2 px-6 py-3 bg-secondary-500/10 hover:bg-secondary-500 text-secondary-600 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all active:scale-95 border border-secondary-500/20"
                >
                    <span class="material-icons-round text-sm">help_outline</span> Panduan
                </button>
                <button onclick="confirmReset()"
                    class="flex items-center gap-2 px-6 py-3 bg-amber-500/10 hover:bg-amber-500 text-amber-600 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all active:scale-95 border border-amber-500/20">
                    <span class="material-icons-round text-sm">refresh</span> Reset Bulan Ini
                </button>
                <button id="btnCetakPdf"
                    class="flex items-center gap-2 px-6 py-3 bg-primary-500/10 hover:bg-primary-500 text-primary-600 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all active:scale-95 border border-primary-500/20">
                    <span class="material-icons-round text-sm">picture_as_pdf</span> Cetak PDF
                </button>
            </div>
        </div>

        @include('user.partials.guide-card', [
            'guideId' => 'riwayat',
            'title' => 'Pahami Catatan dan Hapus Bulanan',
            'description' => 'Halaman ini membantu Anda menelusuri transaksi, mengekspor laporan, dan mengelola data bulan berjalan dengan lebih aman.',
            'items' => [
                'Filter hanya membantu mencari transaksi, tidak mengubah data yang tersimpan.',
                'Cetak PDF akan mengekspor data sesuai filter yang sedang aktif.',
                'Reset Bulan Ini hanya menghapus transaksi pada bulan berjalan.',
                'Setelah reset, saldo dihitung ulang dari transaksi bulan lain yang masih tersisa.',
            ],
        ])

        {{-- Filter Section --}}
        <div class="space-y-6">
            {{-- Type Chips --}}
            <div class="flex items-center gap-3 overflow-x-auto pb-2 no-scrollbar">
                <button onclick="setTypeFilter('')" id="chip-all" class="chip-filter active flex items-center gap-2">
                    <span class="material-icons-round text-sm">apps</span> Semua
                </button>
                <button onclick="setTypeFilter('pemasukan')" id="chip-pemasukan" class="chip-filter flex items-center gap-2">
                    <span class="material-icons-round text-sm">arrow_downward</span> Uang Masuk
                </button>
                <button onclick="setTypeFilter('pengeluaran')" id="chip-pengeluaran" class="chip-filter flex items-center gap-2">
                    <span class="material-icons-round text-sm">arrow_upward</span> Uang Keluar
                </button>
            </div>

            {{-- Search & Date Form --}}
            <div
                class="bg-surface-light dark:bg-surface-dark p-8 rounded-[40px] border border-slate-100 dark:border-slate-800 shadow-card">
                <form id="filter-form" class="grid grid-cols-1 md:grid-cols-12 gap-8 items-center">
                    <div class="md:col-span-6 space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Cari
                            Catatan</label>
                        <div class="relative group">
                            <span
                                class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">search</span>
                            <input type="text" id="search" placeholder="Jenis atau catatan..."
                                class="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-slate-800/40 border-none rounded-2xl text-sm font-bold text-slate-700 dark:text-white focus:ring-2 focus:ring-primary-500/50 transition-all">
                        </div>
                    </div>
                    <div class="md:col-span-4 space-y-3">
                        <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Pilih
                            Tanggal</label>
                        <div class="relative group">
                            <span
                                class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">calendar_month</span>
                            <input type="date" id="tanggal"
                                class="w-full pl-14 pr-6 py-5 bg-slate-50 dark:bg-slate-800/40 border-none rounded-2xl text-sm font-bold text-slate-700 dark:text-white focus:ring-2 focus:ring-primary-500/50 transition-all">
                        </div>
                    </div>
                    <div class="md:col-span-2 flex items-end h-full pt-6">
                        <button type="button" onclick="resetFilter()"
                            class="w-full flex items-center justify-center gap-2 px-6 py-5 bg-slate-100 dark:bg-white/5 text-slate-400 hover:text-red-500 font-black text-[10px] uppercase tracking-widest rounded-2xl transition-all border border-transparent hover:border-red-500/20">
                            <span class="material-icons-round text-sm">filter_alt_off</span>
                            Reset
                        </button>
                    </div>
                    <input type="hidden" id="tipe" value="">
                </form>
            </div>
        </div>

        {{-- Table Card --}}
        <div
            class="bg-surface-light dark:bg-surface-dark rounded-[40px] shadow-card border border-slate-100 dark:border-slate-800 overflow-hidden">
            <div id="table-wrapper">
                @include('user._table')
            </div>
        </div>
    </div>

    <form id="delete-form" method="POST" style="display: none;">@csrf</form>
    <form id="reset-form" action="{{ route('resetSaldo') }}" method="POST" style="display: none;">@csrf
        <input type="hidden" name="password" id="reset-password-input">
    </form>
@endsection

    @section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.kasSakuGuide?.initGuide('riwayat');
        });

        function confirmReset() {
            Swal.fire({
                title: 'Verifikasi Password',
                text: "Catatan bulan ini akan dihapus, catatan bulan lain tetap ada, dan uangmu akan dihitung ulang dari data yang tersisa.",
                icon: 'warning',
                html: `
                        <div class="space-y-4 pt-4 text-left">
                            <p class="text-[11px] text-slate-500 leading-relaxed px-2">
                                Reset ini hanya berlaku untuk transaksi bulan berjalan. Histori bulan lain tetap aman, dan saldo akan dihitung ulang dari transaksi yang masih tersisa.
                            </p>
                            <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Password Anda</label>
                            <div class="relative group">
                                <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">lock</span>
                                <input type="password" id="swal-reset-password" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-5 !pl-14 !pr-6 !font-bold !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all text-sm" placeholder="••••••••">
                            </div>
                        </div>
                    `,
                showCancelButton: true,
                confirmButtonColor: '#f59e0b',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Reset',
                cancelButtonText: 'Batal',
                background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                preConfirm: () => {
                    const password = document.getElementById('swal-reset-password').value;
                    if (!password) {
                        Swal.showValidationMessage('Password wajib diisi!');
                    }
                    return password;
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('reset-password-input').value = result.value;
                    document.getElementById('reset-form').submit();
                }
            });
        }

        function confirmDelete(id) {
            Swal.fire({
                title: 'Hapus Catatan?',
                text: "Tindakan ini tidak dapat dibatalkan.",
                icon: 'error',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#94a3b8',
                confirmButtonText: 'Ya, Hapus',
                cancelButtonText: 'Batal',
                background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
            }).then((result) => {
                if (result.isConfirmed) {
                    const form = document.getElementById('delete-form');
                    form.action = `{{ url('user/riwayat/hapus') }}/${id}`;
                    form.submit();
                }
            });
        }

        function setTypeFilter(type) {
            document.getElementById('tipe').value = type;

            // Update UI
            document.querySelectorAll('.chip-filter').forEach(el => el.classList.remove('active'));
            if (type === '') document.getElementById('chip-all').classList.add('active');
            else if (type === 'pemasukan') document.getElementById('chip-pemasukan').classList.add('active');
            else if (type === 'pengeluaran') document.getElementById('chip-pengeluaran').classList.add('active');

            fetchFilteredData();
        }

        function fetchFilteredData(page = 1) {
            const wrapper = document.getElementById('table-wrapper');
            const search = document.getElementById('search').value;
            const tipe = document.getElementById('tipe').value;
            const tanggal = document.getElementById('tanggal').value;

            // Show Skeleton
            wrapper.innerHTML = `@include('user.skeletons.riwayat')`;

            fetch(`{{ url('user/riwayat') }}?search=${search}&tipe=${tipe}&tanggal=${tanggal}&page=${page}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            }).then(res => res.text()).then(html => {
                wrapper.innerHTML = html;
            });
        }

        document.getElementById('search').oninput = () => fetchFilteredData();
        document.getElementById('tanggal').onchange = () => fetchFilteredData();

        function resetFilter() {
            document.getElementById('search').value = '';
            document.getElementById('tanggal').value = '';
            setTypeFilter('');
        }

        // Pagination handling (Vanilla JS)
        document.addEventListener('click', function (e) {
            const paginationLink = e.target.closest('#pagination-links a');
            if (paginationLink) {
                e.preventDefault();
                const url = paginationLink.getAttribute('href');
                if (url) {
                    const page = url.split('page=')[1];
                    fetchFilteredData(page);
                }
            }
        });

        const btnCetakPdf = document.getElementById('btnCetakPdf');
        btnCetakPdf.onclick = function () {
            const search = document.getElementById('search').value;
            const tipe = document.getElementById('tipe').value;
            const tanggal = document.getElementById('tanggal').value;

            // UI Feedback
            const originalContent = this.innerHTML;
            this.innerHTML = '<span class="material-icons-round text-sm animate-spin">sync</span> Mengolah...';
            this.classList.add('opacity-75', 'cursor-not-allowed');
            this.disabled = true;

            const exportUrl = `{{ url('user/laporan/pdf') }}?search=${search}&tipe=${tipe}&tanggal=${tanggal}`;
            window.location.href = exportUrl;

            // Simple timeout to reset button since download redirection won't trigger a page reload
            setTimeout(() => {
                this.innerHTML = originalContent;
                this.classList.remove('opacity-75', 'cursor-not-allowed');
                this.disabled = false;
            }, 3000);
        };

        // Realtime refresh when balance changes (e.g. from Android transaction)
        (function() {
            let riwayatInitialLoad = true;
            let riwayatSyncTimer = null;

            window.addEventListener('balanceUpdated', () => {
                if (riwayatInitialLoad) {
                    riwayatInitialLoad = false;
                    return;
                }

                clearTimeout(riwayatSyncTimer);
                riwayatSyncTimer = setTimeout(() => {
                    const page = new URLSearchParams(window.location.search).get('page') || 1;
                    fetchFilteredData(page);
                }, 1000); // 1s debounce
            });
        })();
    </script>
@endsection
