<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
    @for($i = 0; $i < 6; $i++)
        <div
            class="bg-surface-light dark:bg-surface-dark rounded-[40px] p-8 border border-slate-100 dark:border-slate-800 shadow-card space-y-6">
            <div class="skeleton h-48 w-full rounded-[32px]"></div>
            <div class="space-y-3">
                <div class="skeleton h-6 w-3/4"></div>
                <div class="skeleton h-4 w-1/2"></div>
            </div>
            <div class="pt-6 border-t border-slate-50 dark:border-slate-800">
                <div class="flex justify-between items-center">
                    <div class="skeleton h-8 w-24"></div>
                    <div class="skeleton h-10 w-10 rounded-full"></div>
                </div>
            </div>
        </div>
    @endfor
</div>