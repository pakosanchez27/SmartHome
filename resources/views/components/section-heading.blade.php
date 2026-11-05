@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class(['flex flex-wrap items-start justify-between gap-3']) }}>
    <div class="min-w-0 space-y-1">
        <h2 class="text-[17px] font-semibold leading-snug text-ink">{{ $title }}</h2>

        @if ($description)
            <p class="text-[13px] leading-relaxed text-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex shrink-0 items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
