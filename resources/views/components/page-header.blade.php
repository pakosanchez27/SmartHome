@props([
    'eyebrow' => null,
    'title',
    'description' => null,
])

<div {{ $attributes->class(['flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between']) }}>
    <div class="min-w-0 space-y-1.5">
        @if ($eyebrow)
            <p class="text-[11px] font-semibold uppercase tracking-[0.08em] text-muted">{{ $eyebrow }}</p>
        @endif

        <h1 class="text-2xl font-semibold leading-tight tracking-tight text-ink sm:text-[28px]">{{ $title }}</h1>

        @if ($description)
            <p class="max-w-2xl text-sm leading-relaxed text-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-col gap-2 sm:flex-row sm:items-center">{{ $actions }}</div>
    @endisset
</div>
