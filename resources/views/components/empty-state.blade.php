@props([
    'icon' => 'book-open',
    'title',
    'description' => null,
    'compact' => false,
])

<div {{ $attributes->class(['flex flex-col items-center justify-center text-center', 'py-8' => ! $compact, 'gap-3' => $compact, 'gap-4' => ! $compact]) }}>
    <span @class([
        'flex items-center justify-center rounded-full bg-primary-soft text-primary',
        'size-14' => ! $compact,
        'size-11' => $compact,
    ])>
        <x-icon :name="$icon" class="size-6" />
    </span>

    <div class="max-w-sm space-y-1.5">
        <p @class(['font-semibold text-ink', 'text-[17px]' => ! $compact, 'text-sm' => $compact])>{{ $title }}</p>

        @if ($description)
            <p class="text-[13px] leading-relaxed text-muted">{{ $description }}</p>
        @endif
    </div>

    @isset($actions)
        <div class="flex flex-wrap items-center justify-center gap-2">{{ $actions }}</div>
    @endisset
</div>
