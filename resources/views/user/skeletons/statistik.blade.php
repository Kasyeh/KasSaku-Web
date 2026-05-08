<div class="max-w-6xl mx-auto space-y-10 animate-pulse">
    {{-- Top Section Skeleton --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 skeleton h-80 rounded-[40px]"></div>
        <div class="grid grid-cols-1 gap-6">
            <div class="skeleton h-36 rounded-3xl"></div>
            <div class="skeleton h-36 rounded-3xl"></div>
        </div>
    </div>

    {{-- Insights Skeleton --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6">
        @for($i = 0; $i < 4; $i++)
            <div class="skeleton h-32 rounded-[32px]"></div>
        @endfor
    </div>

    {{-- Chart & Activity Skeleton --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <div class="lg:col-span-2 skeleton h-[450px] rounded-[40px]"></div>
        <div class="skeleton h-[450px] rounded-[40px]"></div>
    </div>
</div>