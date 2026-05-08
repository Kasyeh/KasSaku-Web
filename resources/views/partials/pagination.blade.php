@if ($paginator->hasPages())
    <div class="flex items-center justify-between px-4 py-3 sm:px-6">
        <div class="flex flex-1 justify-between sm:hidden">
            @if ($paginator->onFirstPage())
                <span class="relative inline-flex items-center px-6 py-3 bg-slate-50 dark:bg-white/5 text-slate-300 text-[10px] font-black uppercase tracking-widest rounded-2xl border border-slate-100 dark:border-slate-800 cursor-default">
                    Sebelumnya
                </span>
            @else
                <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center px-6 py-3 bg-primary-500/10 hover:bg-primary-500 text-primary-600 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all active:scale-95 border border-primary-500/20">
                    Sebelumnya
                </a>
            @endif

            @if ($paginator->hasMorePages())
                <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center px-6 py-3 bg-primary-500/10 hover:bg-primary-500 text-primary-600 hover:text-white text-[10px] font-black uppercase tracking-widest rounded-2xl transition-all active:scale-95 border border-primary-500/20">
                    Berikutnya
                </a>
            @else
                <span class="relative inline-flex items-center px-6 py-3 bg-slate-50 dark:bg-white/5 text-slate-300 text-[10px] font-black uppercase tracking-widest rounded-2xl border border-slate-100 dark:border-slate-800 cursor-default">
                    Berikutnya
                </span>
            @endif
        </div>
        <div class="hidden sm:flex sm:flex-1 sm:items-center sm:justify-between">
            <div>
                <p class="text-[10px] font-black uppercase tracking-widest text-slate-400">
                    Menampilkan <span class="text-slate-700 dark:text-slate-200">{{ $paginator->firstItem() }}</span> - <span class="text-slate-700 dark:text-slate-200">{{ $paginator->lastItem() }}</span> dari <span class="text-slate-700 dark:text-slate-200">{{ $paginator->total() }}</span> Data
                </p>
            </div>
            <div>
                <nav class="isolate inline-flex -space-x-px rounded-2xl shadow-sm gap-2" aria-label="Pagination">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <span class="relative inline-flex items-center p-2.5 bg-slate-50 dark:bg-white/5 text-slate-300 rounded-xl border border-slate-100 dark:border-slate-800 cursor-default">
                            <span class="material-icons-round text-sm">chevron_left</span>
                        </span>
                    @else
                        <a href="{{ $paginator->previousPageUrl() }}" class="relative inline-flex items-center p-2.5 bg-white dark:bg-surface-dark text-slate-500 dark:text-slate-400 hover:bg-primary-500 hover:text-white rounded-xl border border-slate-100 dark:border-slate-800 transition-all active:scale-90">
                            <span class="material-icons-round text-sm">chevron_left</span>
                        </a>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <span class="relative inline-flex items-center px-4 py-2 text-[10px] font-black text-slate-400">
                                {{ $element }}
                            </span>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <span aria-current="page" class="relative z-10 inline-flex items-center px-4 py-2 bg-primary-500 text-white text-[10px] font-black uppercase tracking-widest rounded-xl shadow-lg shadow-primary-500/20">
                                        {{ $page }}
                                    </span>
                                @else
                                    <a href="{{ $url }}" class="relative inline-flex items-center px-4 py-2 bg-white dark:bg-surface-dark text-slate-600 dark:text-slate-400 hover:bg-slate-50 dark:hover:bg-white/5 text-[10px] font-black uppercase tracking-widest rounded-xl border border-slate-100 dark:border-slate-800 transition-all active:scale-90">
                                        {{ $page }}
                                    </a>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <a href="{{ $paginator->nextPageUrl() }}" class="relative inline-flex items-center p-2.5 bg-white dark:bg-surface-dark text-slate-500 dark:text-slate-400 hover:bg-primary-500 hover:text-white rounded-xl border border-slate-100 dark:border-slate-800 transition-all active:scale-90">
                            <span class="material-icons-round text-sm">chevron_right</span>
                        </a>
                    @else
                        <span class="relative inline-flex items-center p-2.5 bg-slate-50 dark:bg-white/5 text-slate-300 rounded-xl border border-slate-100 dark:border-slate-800 cursor-default">
                            <span class="material-icons-round text-sm">chevron_right</span>
                        </span>
                    @endif
                </nav>
            </div>
        </div>
    </div>
@endif
