@extends('template.master')

@section('title', 'Admin - Umpan Balik')
@section('header', 'Umpan Balik Pengguna')

@section('content')
<div class="space-y-6">
  <!-- Card Header -->
  <div class="bg-white dark:bg-[#1e1e2d] rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-white/5">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
      <div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-white">Daftar Saran & Masukan</h2>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">Saran, kritik, dan laporan dari pengguna KasSaku</p>
      </div>
    </div>
  </div>

  <!-- Content -->
  <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
    @forelse($feedbacks as $fb)
    <div class="bg-white dark:bg-[#1e1e2d] rounded-2xl p-6 shadow-sm border border-slate-100 dark:border-white/5 flex flex-col transition-all hover:shadow-md {{ $fb->is_read ? 'opacity-80' : 'border-l-4 border-l-amber-500' }}">
      <div class="flex justify-between items-start mb-4">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-slate-100 dark:bg-white/5 flex items-center justify-center flex-shrink-0">
            <span class="material-icons-round text-slate-400">person</span>
          </div>
          <div>
            <h3 class="font-bold text-slate-800 dark:text-white">{{ $fb->user->username ?? 'User Dihapus' }}</h3>
            <span class="text-xs text-slate-400">{{ \Carbon\Carbon::parse($fb->created_at)->diffForHumans() }}</span>
          </div>
        </div>
        @if($fb->rating)
        <div class="flex items-center bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-lg">
          <span class="material-icons-round text-amber-500 text-sm mr-1">star</span>
          <span class="text-xs font-bold text-amber-600 dark:text-amber-500">{{ $fb->rating }}/5</span>
        </div>
        @endif
      </div>
      
      <div class="flex-1 mb-4">
        <h4 class="font-bold text-slate-800 dark:text-white text-sm mb-2">{{ $fb->subjek }}</h4>
        <p class="text-sm text-slate-600 dark:text-slate-400">{{ $fb->pesan }}</p>
      </div>
      
      <div class="mt-auto pt-4 border-t border-slate-100 dark:border-white/5 flex justify-end">
        @if(!$fb->is_read)
        <button onclick="markAsRead({{ $fb->id }})" class="text-xs font-bold text-indigo-600 dark:text-indigo-400 hover:text-indigo-700 bg-indigo-50 dark:bg-indigo-500/10 px-4 py-2 rounded-lg transition-colors flex items-center gap-2">
          <span class="material-icons-round text-sm">done_all</span>
          Tandai Dibaca
        </button>
        @else
        <span class="text-xs font-medium text-slate-400 flex items-center gap-1">
          <span class="material-icons-round text-sm">check</span>
          Sudah Dibaca
        </span>
        @endif
      </div>
    </div>
    @empty
    <div class="col-span-full bg-white dark:bg-[#1e1e2d] rounded-2xl p-12 text-center border border-slate-100 dark:border-white/5">
      <div class="w-16 h-16 bg-slate-50 dark:bg-white/5 rounded-full flex items-center justify-center mx-auto mb-4">
        <span class="material-icons-round text-3xl text-slate-400">mark_email_read</span>
      </div>
      <h3 class="text-lg font-bold text-slate-800 dark:text-white mb-1">Belum Ada Umpan Balik</h3>
      <p class="text-slate-500 dark:text-slate-400 text-sm">Kotak masuk saran dan masukan masih kosong.</p>
    </div>
    @endforelse
  </div>
</div>
@endsection

@section('scripts')
<script>
  function markAsRead(id) {
    fetch(`/admin/feedback/mark-read/${id}`, {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'X-CSRF-TOKEN': '{{ csrf_token() }}'
      }
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        window.location.reload();
      }
    });
  }
</script>
@endsection
