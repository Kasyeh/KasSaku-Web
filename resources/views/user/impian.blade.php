@extends('template.masteru')

@section('page_title', 'Tabungan Impian 🌟')
@section('page_subtitle', 'Target menabung and barang yang ingin Anda miliki')

@section('content')
    <div class="max-w-6xl mx-auto space-y-10 animate-fade-in transition-all">
        @if($errors->any())
            <div class="p-5 rounded-2xl bg-rose-50 border border-rose-200 text-rose-600 text-sm font-semibold">
                {{ $errors->first() }}
            </div>
        @endif

        <!-- Top Action Bar -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="relative w-full max-w-md group">
                <span
                    class="material-icons-round absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 group-focus-within:text-primary-500 transition-colors">search</span>
                <input type="text" id="search-impian" placeholder="Cari barang impian..."
                    class="w-full pl-12 pr-6 py-4 bg-surface-light dark:bg-surface-dark border-none rounded-[24px] shadow-card focus:ring-2 focus:ring-primary-500/50 transition-all text-sm font-bold text-slate-700 dark:text-white">
            </div>

            <div class="flex items-center gap-3">
                <button
                    type="button"
                    data-guide-open="impian"
                    class="flex items-center justify-center gap-2 px-6 py-4 bg-secondary-500/10 hover:bg-secondary-500 text-secondary-600 hover:text-white font-black rounded-[24px] border border-secondary-500/20 transition-all active:scale-95 text-[10px] uppercase tracking-widest whitespace-nowrap"
                >
                    <span class="material-icons-round text-lg">help_outline</span>
                    Panduan
                </button>
                <a href="{{ url('user/impian/tambah') }}"
                    class="flex items-center justify-center gap-2 px-8 py-4 bg-primary-500 hover:bg-primary-600 text-white font-black rounded-[24px] shadow-lg shadow-primary-500/25 transition-all active:scale-95 text-xs uppercase tracking-widest whitespace-nowrap">
                    <span class="material-icons-round text-lg">add_circle</span>
                    Tambah Tabungan Impian
                </a>
            </div>
        </div>

        @include('user.partials.guide-card', [
            'guideId' => 'impian',
            'title' => 'Cara Kerja Tabungan Impian',
            'description' => 'Fitur ini membantu Anda menabung untuk target tertentu sambil menjaga histori keuangan tetap rapi.',
            'items' => [
                'Tabungan impian adalah target barang atau tujuan yang ingin kamu capai.',
                'Menambah tabungan akan mengurangi uangmu karena dicatat sebagai belanja khusus tabungan impian.',
                'Progress impian dihitung dari total setoran dibandingkan target harga barang.',
                'Menghapus impian tidak otomatis mengembalikan saldo setoran lama karena belum ada mekanisme refund khusus.',
            ],
        ])

        <!-- Grid Items -->
        <div id="table-wrapper">
            @include('user._impian')
        </div>

    </div>

    <!-- Setor Modal -->
    <div id="modalSetor"
        class="fixed inset-0 z-[110] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden animate-fade-in transition-all">
        <div
            class="bg-surface-light dark:bg-surface-dark w-full max-w-lg rounded-[32px] shadow-2xl overflow-hidden transform scale-95 transition-all duration-300 p-8">
            <div class="flex items-center justify-between mb-6">
                <div>
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Tambah Tabungan</p>
                    <h3 id="setorTitle" class="text-xl font-black text-slate-800 dark:text-white mt-1"></h3>
                </div>
                <button
                    class="close-setor-modal w-9 h-9 rounded-full bg-slate-100 dark:bg-white/10 text-slate-500 dark:text-slate-300 hover:bg-slate-200 dark:hover:bg-white/20 transition-all">
                    <span class="material-icons-round text-lg">close</span>
                </button>
            </div>
            <form id="formSetorImpian" method="POST" class="space-y-6">
                @csrf
                <input type="hidden" name="nominal" id="setorNominalReal">

                <div
                    class="flex items-center justify-between bg-slate-50 dark:bg-slate-800/40 p-5 rounded-3xl border border-slate-100 dark:border-white/5">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Uang Saya Saat Ini</p>
                    <p class="text-base font-black text-emerald-500">Rp <span class="rt-balance">{{ number_format($saldo ?? 0, 0, ',', '.') }}</span></p>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Nominal
                        Jumlah Tabungan</label>
                    <div class="relative group">
                        <span class="absolute left-6 top-1/2 -translate-y-1/2 text-primary-500 font-black text-xl">Rp</span>
                        <input type="text" id="setorNominalDisplay" placeholder="0"
                            class="w-full bg-slate-50 dark:bg-slate-800/40 border-none rounded-3xl py-6 pl-16 pr-8 font-black text-3xl text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/50 transition-all tracking-tighter">
                    </div>
                    <div class="flex items-center justify-between px-2">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest">Sisa Target</p>
                        <p id="setorSisaTarget" class="text-xs font-black text-primary-600 dark:text-primary-400"></p>
                    </div>
                </div>

                <div class="space-y-4">
                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Keterangan
                        (Opsional)</label>
                    <div class="relative group">
                        <span
                            class="material-icons-round absolute left-5 top-5 text-slate-300 group-focus-within:text-primary-500 transition-colors">notes</span>
                        <textarea name="keterangan" rows="3"
                            class="w-full bg-slate-50 dark:bg-slate-800/40 border-none rounded-2xl py-4 pl-14 pr-6 font-bold text-sm text-slate-800 dark:text-white focus:ring-2 focus:ring-primary-500/50 transition-all resize-none"
                            placeholder="Contoh: Alokasi bonus bulanan"></textarea>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button type="button"
                        class="close-setor-modal py-3 rounded-2xl bg-slate-100 dark:bg-white/10 text-slate-600 dark:text-slate-300 font-black text-xs uppercase tracking-widest">
                        Batal
                    </button>
                    <button type="submit"
                        class="py-3 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-black text-xs uppercase tracking-widest transition-all">
                        Simpan Tabungan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Detailed Modal -->
    <div id="modalDetail"
        class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm hidden animate-fade-in transition-all">
        <div
            class="bg-surface-light dark:bg-surface-dark w-full max-w-xl rounded-[40px] shadow-2xl overflow-hidden transform scale-95 transition-all duration-300">
            <div class="relative h-72">
                <img id="detailFoto" class="w-full h-full object-cover" alt="Detail Barang">
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/20 to-transparent"></div>
                <button
                    class="close-modal absolute top-6 right-6 w-10 h-10 bg-white/20 backdrop-blur-md rounded-full flex items-center justify-center text-white hover:bg-white hover:text-slate-900 transition-all">
                    <span class="material-icons-round">close</span>
                </button>
                <div class="absolute bottom-8 left-8 right-8">
                    <p class="text-[10px] font-black text-primary-400 uppercase tracking-[0.3em] mb-2">Target Tabungan</p>
                    <h2 id="detailNama" class="text-3xl font-black text-white tracking-tight"></h2>
                </div>
            </div>
            <div class="p-10 space-y-8">
                <div class="grid grid-cols-2 gap-8">
                    <div class="space-y-1 p-6 bg-slate-50 dark:bg-white/5 rounded-[32px]">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Target Harga</p>
                        <p id="detailHarga" class="text-xl font-black text-primary-600 dark:text-primary-400"></p>
                    </div>
                    <div class="space-y-1 p-6 bg-slate-50 dark:bg-white/5 rounded-[32px]">
                        <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-1">Deadline</p>
                        <p id="detailDeadline" class="text-base font-bold text-slate-700 dark:text-slate-200"></p>
                    </div>
                </div>
                <div class="pt-2">
                    <p class="text-[10px] font-black text-slate-400 uppercase tracking-widest mb-3 ml-2">Catatan</p>
                    <div class="p-6 bg-slate-50 dark:bg-white/5 rounded-[32px] border border-slate-100 dark:border-white/5">
                        <p id="detailKeterangan"
                            class="text-sm font-medium text-slate-500 dark:text-slate-400 leading-relaxed italic"></p>
                    </div>
                </div>
                <button
                    class="close-modal w-full py-5 bg-slate-100 dark:bg-white/5 text-slate-600 dark:text-slate-300 font-black uppercase tracking-widest text-xs rounded-2xl hover:bg-slate-200 dark:hover:bg-white/10 transition-all">
                    Tutup Detail
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            window.kasSakuGuide?.initGuide('impian');

            const searchInput = document.getElementById('search-impian');
            const wrapper = document.getElementById('table-wrapper');
            const modal = document.getElementById('modalDetail');
            const setorModal = document.getElementById('modalSetor');
            const setorForm = document.getElementById('formSetorImpian');
            const setorTitle = document.getElementById('setorTitle');
            const setorSisaTarget = document.getElementById('setorSisaTarget');
            const setorNominalDisplay = document.getElementById('setorNominalDisplay');
            const setorNominalReal = document.getElementById('setorNominalReal');

            const formatRupiah = (value) => {
                const number = Number(value || 0);
                return 'Rp ' + number.toLocaleString('id-ID');
            };

            const normalizeNumber = (value) => value.replace(/\D/g, '');

            // Live Search
            searchInput.oninput = function () {
                wrapper.innerHTML = `@include('user.skeletons.impian')`;
                fetch(`{{ url('user/impian') }}?search=${this.value}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                }).then(res => res.text()).then(html => {
                    wrapper.innerHTML = html;
                    initInteractions();
                });
            };

            function initInteractions() {
                // Detail Trigger
                document.querySelectorAll('.btn-detail-impian').forEach(btn => {
                    btn.onclick = function () {
                        const d = this.dataset;
                        document.getElementById('detailNama').innerText = d.nama;
                        document.getElementById('detailHarga').innerText = 'Rp ' + Number(d.harga).toLocaleString('id-ID');
                        document.getElementById('detailDeadline').innerText = d.deadline;
                        document.getElementById('detailKeterangan').innerText = d.keterangan || 'Tidak ada catatan tambahan.';
                        const img = document.getElementById('detailFoto');
                        img.src = d.foto || '';
                        img.style.display = d.foto ? 'block' : 'none';

                        modal.classList.remove('hidden');
                        modal.querySelector('div').classList.replace('scale-95', 'scale-100');

                        // Trigger Confetti if 100% complete
                        if (Number(d.progress) >= 100 || Number(d.sisa) <= 0) {
                            setTimeout(() => {
                                if (typeof window.confetti === 'function') {
                                    window.confetti({
                                        particleCount: 150,
                                        spread: 80,
                                        origin: { y: 0.6 },
                                        colors: ['#10b981', '#f59e0b', '#3b82f6', '#ec4899', '#8b5cf6']
                                    });
                                }
                            }, 300);
                        }
                    };
                });

                // Setoran Trigger
                document.querySelectorAll('.btn-setor-impian').forEach(btn => {
                    btn.onclick = function () {
                        const data = this.dataset;
                        setorForm.action = `{{ url('/user/impian') }}/${data.id}/setoran`;
                        setorTitle.innerText = data.nama || 'Impian';
                        setorSisaTarget.innerText = formatRupiah(data.sisa || 0);
                        setorNominalDisplay.value = '';
                        setorNominalReal.value = '';
                        setorForm.querySelector('textarea[name="keterangan"]').value = '';

                        setorModal.classList.remove('hidden');
                        setorModal.querySelector('div').classList.replace('scale-95', 'scale-100');
                    };
                });

                // Delete Confirm
                document.querySelectorAll('.form-hapus-impian').forEach(form => {
                    form.onsubmit = function (e) {
                        e.preventDefault();
                        Swal.fire({
                            title: 'Verifikasi Password',
                            text: 'Masukkan password Anda untuk mengonfirmasi penghapusan.',
                            html: `
                                <div class="space-y-4 pt-4 text-left">
                                    <label class="text-[10px] font-black text-slate-400 uppercase tracking-[0.2em] ml-2">Password Anda</label>
                                    <div class="relative group">
                                        <span class="material-icons-round absolute left-5 top-1/2 -translate-y-1/2 text-slate-300 group-focus-within:text-primary-500 transition-colors">lock</span>
                                        <input type="password" id="swal-hapus-password" class="!m-0 !w-full !bg-slate-50 dark:!bg-slate-800/40 !border-none !rounded-2xl !py-5 !pl-14 !pr-6 !font-bold !text-slate-800 dark:!text-white focus:!ring-2 focus:!ring-primary-500/50 transition-all text-sm" placeholder="••••••••">
                                    </div>
                                </div>
                            `,
                            icon: 'warning',
                            showCancelButton: true,
                            confirmButtonColor: '#ef4444',
                            confirmButtonText: 'Ya, Hapus',
                            cancelButtonText: 'Batal',
                            background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                            preConfirm: () => {
                                const password = document.getElementById('swal-hapus-password').value;
                                if (!password) {
                                    Swal.showValidationMessage('Password wajib diisi');
                                }
                                return password;
                            }
                        }).then(res => {
                            if (res.isConfirmed) {
                                this.querySelector('.input-password-hapus').value = res.value;
                                this.submit();
                            }
                        });
                    };
                });
            }

            setorNominalDisplay.oninput = function () {
                const numericValue = normalizeNumber(this.value);
                setorNominalReal.value = numericValue;
                this.value = numericValue ? Number(numericValue).toLocaleString('id-ID') : '';
            };

            setorForm.onsubmit = function (e) {
                if (!setorNominalReal.value || Number(setorNominalReal.value) < 1) {
                    e.preventDefault();
                    Swal.fire({
                        icon: 'warning',
                        title: 'Nominal belum valid',
                        text: 'Masukkan nominal setoran minimal Rp 1.',
                    });
                }
            };

            document.querySelectorAll('.close-setor-modal').forEach(btn => {
                btn.onclick = () => {
                    setorModal.querySelector('div').classList.replace('scale-100', 'scale-95');
                    setTimeout(() => setorModal.classList.add('hidden'), 200);
                };
            });

            document.querySelectorAll('.close-modal').forEach(btn => {
                btn.onclick = () => {
                    modal.querySelector('div').classList.replace('scale-100', 'scale-95');
                    setTimeout(() => modal.classList.add('hidden'), 200);
                };
            });

            initInteractions();

            // Realtime refresh when balance changes (e.g. from Android transaction)
            (function() {
                let impianInitialLoad = true;
                let impianSyncTimer = null;

                window.addEventListener('balanceUpdated', () => {
                    if (impianInitialLoad) {
                        impianInitialLoad = false;
                        return;
                    }

                    clearTimeout(impianSyncTimer);
                    impianSyncTimer = setTimeout(() => {
                        const search = searchInput.value;
                        wrapper.innerHTML = `@include('user.skeletons.impian')`;
                        fetch(`{{ url('user/impian') }}?search=${search}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        }).then(res => res.text()).then(html => {
                            wrapper.innerHTML = html;
                            initInteractions();
                        });
                    }, 1000); // 1s debounce to ensure DB has settled
                });
            })();
        });
    </script>
@endsection
