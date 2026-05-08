@extends('template.master')

@section('page_title', 'Decision Inbox 📥')
@section('page_subtitle', 'Tinjau dan pulihkan akses pengguna yang memerlukan perhatian administrasi')

@section('content')
    <div class="space-y-10">
        <!-- Page Header Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <div class="px-5 py-3 bg-indigo-500/10 border border-indigo-500/20 rounded-2xl shadow-glow">
                <span class="text-[10px] font-black text-indigo-700 dark:text-indigo-400 uppercase tracking-widest">
                    {{ $requests->where('status', 'pending')->count() }} Active Requests
                </span>
            </div>

            <!-- Bulk Delete Action (Hidden by default) -->
            <div id="bulk-action-container" class="hidden transition-all duration-300">
                <button type="button" onclick="confirmBulkDelete()" 
                    class="flex items-center gap-2 px-6 py-3 bg-rose-500 text-white rounded-2xl font-black text-xs uppercase tracking-widest hover:bg-rose-600 shadow-lg shadow-rose-500/20 transition-all hover:-translate-y-1">
                    <span class="material-icons-round text-lg">delete_sweep</span>
                    Hapus <span id="selected-count">0</span> Data Terpilih
                </button>
            </div>
            
            <form id="bulk-delete-form" action="{{ route('admin.bulk_hapus_permintaan_unblock') }}" method="POST" style="display: none;">
                @csrf
                <div id="bulk-ids-container"></div>
            </form>
        </div>

        <!-- Premium Inbox Table Card -->
        <div class="card-premium rounded-[2.5rem] overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full align-middle">
                    <thead>
                        <tr class="bg-slate-50/50 dark:bg-white/5 border-b border-slate-50 dark:border-white/5">
                            <th class="px-8 py-5 text-center" style="width: 50px;">
                                <div class="flex items-center justify-center">
                                    <input type="checkbox" id="select-all" 
                                        class="w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:border-slate-700 cursor-pointer">
                                </div>
                            </th>
                            <th class="px-4 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 80px;">Order</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 200px;">User Identity</th>
                            <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Alasan / Pesan</th>
                            <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 150px;">Status</th>
                            <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 200px;">Decision</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                        @forelse($requests as $req)
                            <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-all group">
                                <td class="px-8 py-6 text-center">
                                    <div class="flex items-center justify-center">
                                        <input type="checkbox" name="request_ids[]" value="{{ $req->id }}" 
                                            class="request-checkbox w-4 h-4 rounded border-slate-300 text-primary-600 focus:ring-primary-500 dark:bg-slate-800 dark:border-slate-700 cursor-pointer">
                                    </div>
                                </td>
                                <td class="px-4 py-6">
                                    <span class="text-xs font-black text-slate-300 dark:text-slate-700">#INF-{{ $loop->iteration }}</span>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="flex flex-col">
                                        <div class="text-sm font-bold text-slate-800 dark:text-white leading-tight">{{ $req->user->username ?? 'Legacy Account' }}</div>
                                        <div class="text-[10px] font-black text-slate-400 uppercase tracking-tighter mt-0.5">
                                            Sent: {{ $req->created_at->translatedFormat('d M, H:i') }}
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6">
                                    <div class="relative group/msg">
                                        <div class="text-sm font-medium text-slate-600 dark:text-slate-400 border-l-4 border-slate-100 dark:border-white/5 ps-4 py-2 italic group-hover:border-primary-500 transition-all leading-relaxed">
                                            "{{ $req->pesan }}"
                                        </div>
                                    </div>
                                </td>
                                <td class="px-8 py-6 text-center">
                                    @if($req->status == 'pending')
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-100 dark:bg-amber-900/20 text-amber-700 dark:text-amber-400 rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500 animate-pulse"></span>
                                            <span class="text-[10px] font-black uppercase tracking-tight">Queued</span>
                                        </div>
                                    @elseif($req->status == 'dikabulkan')
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-100 dark:bg-primary-900/20 text-primary-700 dark:text-primary-400 rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                                            <span class="text-[10px] font-black uppercase tracking-tight">Resolved</span>
                                        </div>
                                    @else
                                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-rose-100 dark:bg-rose-900/20 text-rose-700 dark:text-rose-400 rounded-lg">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span>
                                            <span class="text-[10px] font-black uppercase tracking-tight">Rejected</span>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-8 py-6 text-right">
                                    @if($req->status == 'pending')
                                        <div class="flex justify-end gap-2 pr-2">
                                            <form action="{{ route('admin.proses_unblock', $req->id) }}" method="POST" class="inline">
                                                @csrf
                                                <input type="hidden" name="action" value="terima">
                                                <button type="submit" class="flex items-center gap-1.5 px-4 py-2 bg-primary-500/10 text-primary-600 dark:text-primary-400 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-primary-500 hover:text-white transition-all shadow-sm">
                                                    <span class="material-icons-round text-base">check_circle</span>
                                                    Approve
                                                </button>
                                            </form>
                                            <button type="button" onclick="rejectUnblock('{{ $req->id }}')" 
                                                class="flex items-center gap-1.5 px-4 py-2 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-xl font-black text-[10px] uppercase tracking-widest hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                                                <span class="material-icons-round text-base">cancel</span>
                                                Reject
                                            </button>
                                            <form id="reject-form-{{ $req->id }}" action="{{ route('admin.proses_unblock', $req->id) }}" method="POST" style="display: none;">
                                                @csrf
                                                <input type="hidden" name="action" value="tolak">
                                                <input type="hidden" name="alasan_admin" id="alasan-{{ $req->id }}">
                                            </form>
                                        </div>
                                    @else
                                        <div class="flex items-center justify-end gap-3 pr-4">
                                            <div class="flex flex-col items-end">
                                                <span class="text-[10px] font-black text-slate-300 dark:text-slate-600 uppercase tracking-widest italic">Archived System</span>
                                                <span class="text-[9px] font-bold text-slate-400">Handled on {{ $req->updated_at->format('d/m/y') }}</span>
                                            </div>
                                            <button type="button" onclick="deleteUnblock('{{ $req->id }}')" 
                                                class="flex items-center justify-center w-8 h-8 bg-rose-500/10 text-rose-600 dark:text-rose-400 rounded-lg hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                                                <span class="material-icons-round text-lg">delete_sweep</span>
                                            </button>
                                            <form id="delete-form-{{ $req->id }}" action="{{ route('admin.hapus_permintaan_unblock', $req->id) }}" method="POST" style="display: none;">
                                                @csrf
                                            </form>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-8 py-24 text-center">
                                    <div class="flex flex-col items-center justify-center">
                                        <div class="w-20 h-20 bg-slate-50 dark:bg-white/5 rounded-[2rem] flex items-center justify-center mb-6 border border-slate-100 dark:border-white/5">
                                            <span class="material-icons-round text-4xl text-slate-200 dark:text-slate-700">mark_email_read</span>
                                        </div>
                                        <h4 class="text-sm font-black text-slate-800 dark:text-white uppercase tracking-tighter">Semua Beres!</h4>
                                        <p class="text-[11px] font-bold text-slate-400 dark:text-slate-500 mt-1 uppercase tracking-widest font-sans">Kotak masuk bersih. Tidak ada permintaan tertunda.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script type="module">
    import { initializeApp, getApp, getApps } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-app.js";
    import { getDatabase, ref, onChildAdded } from "https://www.gstatic.com/firebasejs/10.7.1/firebase-database.js";

    const firebaseConfig = {
      databaseURL: "https://kassaku-8beb0-default-rtdb.asia-southeast1.firebasedatabase.app",
    };

    const app = getApps().length ? getApp() : initializeApp(firebaseConfig);
    const db = getDatabase(app);
    const unblockRequestsRef = ref(db, 'admin/unblock_requests');
    let pendingWatermark = Number("{{ (int) ($requests->where('status', 'pending')->max(fn ($req) => optional($req->created_at)->timestamp) ?? 0) }}");

    onChildAdded(unblockRequestsRef, (snapshot) => {
      const data = snapshot.val();
      const requestTimestamp = Number(data?.timestamp ?? 0);

      if (data?.status === 'pending' && requestTimestamp > pendingWatermark) {
        pendingWatermark = requestTimestamp;
        window.location.reload();
      }
    });
</script>

<script>
    let pendingInboxWatermark = Number("{{ (int) ($requests->where('status', 'pending')->max(fn ($req) => optional($req->created_at)->timestamp) ?? 0) }}");
    let pendingInboxPollingBusy = false;

    async function pollPendingInboxFeed() {
        if (pendingInboxPollingBusy) {
            return;
        }

        pendingInboxPollingBusy = true;

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

            if (latestTimestamp > pendingInboxWatermark) {
                pendingInboxWatermark = latestTimestamp;
                window.location.reload();
            }
        } catch (error) {
            console.error('Pending inbox polling failed:', error);
        } finally {
            pendingInboxPollingBusy = false;
        }
    }

    pollPendingInboxFeed();
    setInterval(pollPendingInboxFeed, 5000);

    function rejectUnblock(id) {
        Swal.fire({
            title: 'Tolak Permintaan',
            text: 'Berikan alasan singkat mengapa permintaan ini ditolak.',
            input: 'textarea',
            inputPlaceholder: 'Tulis alasan penolakan di sini...',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            confirmButtonText: 'Tolak Sekarang',
            cancelButtonText: 'Batal',
            background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold uppercase tracking-wider',
                cancelButton: 'rounded-xl px-6 py-2.5 font-bold uppercase tracking-wider'
            },
            preConfirm: (alasan) => {
                if (!alasan) {
                    Swal.showValidationMessage('Alasan penolakan wajib diisi');
                }
                return alasan;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('alasan-' + id).value = result.value;
                document.getElementById('reject-form-' + id).submit();
            }
        });
    }

    function deleteUnblock(id) {
        Swal.fire({
            title: 'Hapus Permintaan?',
            text: 'Tindakan ini akan menghapus catatan permintaan ini secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            confirmButtonText: 'Ya, Hapus!',
            cancelButtonText: 'Batal',
            background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold uppercase tracking-wider',
                cancelButton: 'rounded-xl px-6 py-2.5 font-bold uppercase tracking-wider'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }

    // Bulk Delete Logic
    const selectAll = document.getElementById('select-all');
    const checkboxes = document.querySelectorAll('.request-checkbox');
    const bulkActionContainer = document.getElementById('bulk-action-container');
    const selectedCountSpan = document.getElementById('selected-count');

    function updateBulkUI() {
        const checkedCount = document.querySelectorAll('.request-checkbox:checked').length;
        if (checkedCount > 0) {
            bulkActionContainer.classList.remove('hidden');
            selectedCountSpan.textContent = checkedCount;
        } else {
            bulkActionContainer.classList.add('hidden');
        }
    }

    if (selectAll) {
        selectAll.addEventListener('change', function() {
            checkboxes.forEach(cb => {
                cb.checked = selectAll.checked;
            });
            updateBulkUI();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            const allChecked = document.querySelectorAll('.request-checkbox:checked').length === checkboxes.length;
            if (selectAll) selectAll.checked = allChecked;
            updateBulkUI();
        });
    });

    window.confirmBulkDelete = function() {
        const checkedIds = Array.from(document.querySelectorAll('.request-checkbox:checked')).map(cb => cb.value);
        
        Swal.fire({
            title: 'Hapus ' + checkedIds.length + ' Permintaan?',
            text: 'Tindakan ini akan menghapus semua catatan yang dipilih secara permanen.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#f43f5e',
            confirmButtonText: 'Ya, Hapus Semua!',
            cancelButtonText: 'Batal',
            background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
            color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
            customClass: {
                popup: 'rounded-3xl',
                confirmButton: 'rounded-xl px-6 py-2.5 font-bold uppercase tracking-wider',
                cancelButton: 'rounded-xl px-6 py-2.5 font-bold uppercase tracking-wider'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                const form = document.getElementById('bulk-delete-form');
                const container = document.getElementById('bulk-ids-container');
                container.innerHTML = '';
                
                checkedIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'ids[]';
                    input.value = id;
                    container.appendChild(input);
                });
                
                form.submit();
            }
        });
    }
</script>
@endsection
