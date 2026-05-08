@extends('template.master')

@section('page_title', 'Galeri Motivasi ✨')
@section('page_subtitle', 'Kurasi konten inspiratif untuk mencerahkan hari-hari pengguna')

@section('content')
    <div class="space-y-10">
        <!-- Page Header Action -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6">
            <a href="{{ url('motivasi/tambah') }}" class="flex items-center gap-2 px-6 py-3.5 bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl shadow-lg shadow-primary-500/20 text-white font-black hover:scale-105 active:scale-95 transition-all group">
                <span class="material-icons-round transition-transform group-hover:rotate-90">add</span>
                <span class="text-sm uppercase tracking-widest">Tambah Konten</span>
            </a>
        </div>

        <!-- Gallery Section -->
        <div class="card-premium rounded-[2.5rem] overflow-hidden">
            @if($motivasi->count() > 0)
                <div class="overflow-x-auto">
                    <table class="w-full align-middle">
                        <thead>
                            <tr class="bg-slate-50/50 dark:bg-white/5 border-b border-slate-50 dark:border-white/5">
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 80px;">No</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 140px;">Format</th>
                                <th class="px-8 py-5 text-left text-[10px] font-black text-slate-400 uppercase tracking-widest">Pesan Inspiratif</th>
                                <th class="px-8 py-5 text-center text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 150px;">Media</th>
                                <th class="px-8 py-5 text-right text-[10px] font-black text-slate-400 uppercase tracking-widest" style="width: 150px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-50 dark:divide-white/5">
                            @foreach($motivasi as $m)
                                <tr class="hover:bg-slate-50/50 dark:hover:bg-white/[0.02] transition-all group">
                                    <td class="px-8 py-6">
                                        <span class="text-xs font-black text-slate-300 dark:text-slate-700 tracking-tighter">REQ-0{{ $loop->iteration }}</span>
                                    </td>
                                    <td class="px-8 py-6">
                                        @if($m->tipe == 'image')
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 dark:bg-indigo-900/10 text-indigo-600 dark:text-indigo-400 rounded-lg">
                                                <span class="material-icons-round text-sm">image</span>
                                                <span class="text-[10px] font-black uppercase tracking-tight">Visual</span>
                                            </div>
                                        @else
                                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 dark:bg-amber-900/10 text-amber-600 dark:text-amber-400 rounded-lg">
                                                <span class="material-icons-round text-sm">text_fields</span>
                                                <span class="text-[10px] font-black uppercase tracking-tight">Textual</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="text-sm font-medium text-slate-700 dark:text-slate-300 italic line-clamp-2 max-w-md leading-relaxed">
                                            "{{ $m->isi ?? 'Konten tanpa deskripsi...' }}"
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex justify-center">
                                            @if($m->foto)
                                                <div class="relative group/media">
                                                    <img src="{{ asset('storage/' . $m->foto) }}" alt="Preview" 
                                                        class="w-16 h-10 object-cover rounded-xl shadow-sm border border-slate-100 dark:border-white/10 group-hover/media:scale-110 transition-transform cursor-zoom-in">
                                                </div>
                                            @else
                                                <div class="w-16 h-10 bg-slate-50 dark:bg-white/5 border border-dashed border-slate-200 dark:border-white/10 rounded-xl flex items-center justify-center text-slate-300">
                                                    <span class="material-icons-round text-sm">visibility_off</span>
                                                </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-8 py-6">
                                        <div class="flex justify-end gap-2 pr-2">
                                            <a href="{{ route('motivasi.edit', $m->id) }}" 
                                                class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-primary-500 hover:bg-primary-500 hover:text-white hover:shadow-glow transition-all" title="Edit Content">
                                                <span class="material-icons-round text-lg">edit_note</span>
                                            </a>
                                            <form action="{{ route('hapusMotivasi', $m->id) }}" method="POST" class="inline delete-form">
                                                @csrf
                                                <button type="button" class="w-8 h-8 flex items-center justify-center bg-slate-50 dark:bg-white/5 border border-slate-100 dark:border-white/5 rounded-xl text-rose-500 hover:bg-rose-500 hover:text-white hover:shadow-lg transition-all delete-button"
                                                    data-id="{{ $m->id }}" title="Evict Content">
                                                    <span class="material-icons-round text-lg">delete_sweep</span>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="flex flex-col items-center justify-center py-24 text-center">
                    <div class="w-24 h-24 bg-slate-50 dark:bg-white/5 rounded-[2rem] flex items-center justify-center mb-6 shadow-sm border border-slate-100 dark:border-white/5">
                        <span class="material-icons-round text-5xl text-slate-200 dark:text-slate-700">inventory_2</span>
                    </div>
                    <h3 class="text-xl font-black text-slate-800 dark:text-white uppercase tracking-tighter">Vault Is Empty</h3>
                    <p class="text-slate-500 dark:text-slate-400 mt-2 font-medium max-w-sm">Belum ada konten motivasi di sistem. Mulai tambahkan untuk meningkatkan user engagement.</p>
                    <a href="{{ url('motivasi/tambah') }}" class="mt-8 px-8 py-3 bg-slate-900 dark:bg-white text-white dark:text-slate-900 rounded-2xl font-black text-xs uppercase tracking-widest hover:scale-105 transition-all">Create First Content</a>
                </div>
            @endif
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Handle delete button click
            const deleteButtons = document.querySelectorAll('.delete-button');

            deleteButtons.forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    const form = this.closest('form');

                    Swal.fire({
                        title: 'Hapus Konten?',
                        text: "Tindakan ini akan melenyapkan pesan inspiratif ini dari dashboard seluruh user.",
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#6b7280',
                        confirmButtonText: 'Ya, Lenyapkan',
                        cancelButtonText: 'Batal',
                        background: document.documentElement.classList.contains('dark') ? '#1e1e2d' : '#fff',
                        color: document.documentElement.classList.contains('dark') ? '#fff' : '#1e1e2d',
                        reverseButtons: true
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
@endsection