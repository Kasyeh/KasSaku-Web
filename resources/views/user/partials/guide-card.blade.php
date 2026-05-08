<div
    data-guide-id="{{ $guideId }}"
    class="guide-card card-premium rounded-[32px] p-6 md:p-8 border border-primary-100/80 dark:border-primary-900/30 bg-gradient-to-br from-primary-50/90 to-white dark:from-primary-900/10 dark:to-surface-dark"
>
    <div class="flex flex-col gap-5 md:flex-row md:items-start md:justify-between">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-2xl bg-primary-500 text-white flex items-center justify-center shadow-lg shadow-primary-500/20 shrink-0">
                <span class="material-icons-round">help_outline</span>
            </div>
            <div class="space-y-3">
                <div>
                    <p class="text-[10px] font-black text-primary-500 uppercase tracking-[0.24em] mb-1">Panduan Singkat</p>
                    <h3 class="text-lg font-black text-slate-800 dark:text-white tracking-tight">{{ $title }}</h3>
                    @if(!empty($description))
                        <p class="text-sm text-slate-500 dark:text-slate-300 mt-2 leading-relaxed">{{ $description }}</p>
                    @endif
                </div>
                <ul class="space-y-2.5">
                    @foreach($items as $item)
                        <li class="flex items-start gap-2.5 text-sm text-slate-600 dark:text-slate-300 leading-relaxed">
                            <span class="material-icons-round text-base text-primary-500 mt-0.5 shrink-0">check_circle</span>
                            <span>{{ $item }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
        <div class="flex justify-end md:justify-start">
            <button
                type="button"
                data-guide-dismiss="{{ $guideId }}"
                class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-primary-500 hover:bg-primary-600 text-white text-[10px] font-black uppercase tracking-[0.18em] transition-all active:scale-95"
            >
                <span class="material-icons-round text-sm">done</span>
                Mengerti
            </button>
        </div>
    </div>
</div>
