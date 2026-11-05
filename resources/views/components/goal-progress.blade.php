@props([
    'name',
    'saved' => 0,
    'target',
    'deadline' => null,
    'contribution' => null,
    'description' => null,
    'icon' => 'target',
    'href' => null,
])

@php
    $saved = max(0, (int) $saved);
    $target = max(0, (int) $target);
    $percent = $target > 0 ? min(100, (int) round(($saved / $target) * 100)) : 0;
    $completed = $target > 0 && $saved >= $target;
    $remaining = max(0, $target - $saved);
@endphp

<article {{ $attributes->class('flex h-full flex-col gap-4 rounded-card border border-border bg-surface p-5 shadow-card') }}>
    <div class="flex items-start gap-3">
        <span @class([
            'flex size-10 shrink-0 items-center justify-center rounded-control',
            'bg-success-soft text-success' => $completed,
            'bg-primary-soft text-primary' => ! $completed,
        ])>
            <x-icon :name="$completed ? 'circle-check' : $icon" class="size-5" />
        </span>

        <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold leading-snug text-ink">
                @if ($href)
                    <a href="{{ $href }}" class="rounded-control transition-colors hover:text-primary">{{ $name }}</a>
                @else
                    {{ $name }}
                @endif
            </h3>

            @if ($description)
                <p class="mt-0.5 text-[12px] leading-relaxed text-muted">{{ $description }}</p>
            @endif
        </div>

        @if ($completed)
            <x-badge tone="success" icon="check" class="shrink-0">Meta alcanzada</x-badge>
        @endif
    </div>

    <div class="space-y-2">
        <div class="flex items-baseline justify-between gap-3">
            <span class="text-[12px] text-muted">Ahorrado</span>
            <span class="text-sm font-semibold tabular-nums text-ink">
                {{ \App\Support\Money::format($saved) }}
                <span class="text-[12px] font-normal text-muted">de {{ \App\Support\Money::format($target) }}</span>
            </span>
        </div>

        <x-progress :value="$percent" :max="100" :tone="$completed ? 'success' : 'primary'" :label="'Avance de '.$name" />
    </div>

    <div class="mt-auto flex flex-wrap items-center justify-between gap-2 border-t border-border pt-3 text-[12px] text-muted">
        <span class="font-medium tabular-nums {{ $completed ? 'text-success' : 'text-ink-soft' }}">
            {{ $completed ? 'Objetivo cumplido' : 'Faltan '.\App\Support\Money::format($remaining) }}
        </span>

        <span class="flex flex-wrap items-center gap-x-3 gap-y-1">
            @if ($deadline)
                <span class="inline-flex items-center gap-1">
                    <x-icon name="calendar-days" class="size-3.5" />
                    {{ $deadline }}
                </span>
            @endif

            @if ($contribution)
                <span class="inline-flex items-center gap-1">
                    <x-icon name="piggy-bank" class="size-3.5" />
                    {{ \App\Support\Money::format((int) $contribution) }} / mes
                </span>
            @endif
        </span>
    </div>
</article>
