@props([
    'name',
    'category' => null,
    'stock' => 0,
    'min' => 0,
    'unit' => 'piezas',
    'location' => null,
    'expiresAt' => null,
    'status' => null,
    'icon' => 'package',
    'price' => null,
    'href' => null,
])

@php
    $stock = (float) $stock;
    $min = (float) $min;

    $status = $status ?? ($stock <= 0 ? 'out' : ($stock <= $min ? 'low' : 'ok'));

    $tones = [
        'ok' => ['badge' => 'success', 'label' => 'En stock', 'bar' => 'success'],
        'low' => ['badge' => 'warning', 'label' => 'Stock bajo', 'bar' => 'warning'],
        'out' => ['badge' => 'danger', 'label' => 'Agotado', 'bar' => 'danger'],
    ];

    $tone = $tones[$status] ?? $tones['ok'];
    $max = max($min * 2, $stock, 1);
    $progress = min(100, (int) round(($stock / $max) * 100));

    $formatQty = function (float $value): string {
        if (abs($value - round($value)) < 0.0001) {
            return (string) (int) round($value);
        }

        return rtrim(rtrim(number_format($value, 2, '.', ''), '0'), '.');
    };
@endphp

<article {{ $attributes->class('flex h-full flex-col gap-3 rounded-card border border-border bg-surface p-4 shadow-card') }}>
    <div class="flex items-start gap-3">
        <span class="flex size-10 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
            <x-icon :name="$icon" class="size-5" />
        </span>

        <div class="min-w-0 flex-1">
            <h3 class="text-sm font-semibold leading-snug text-ink">
                @if ($href)
                    <a href="{{ $href }}" class="rounded-control transition-colors hover:text-primary">{{ $name }}</a>
                @else
                    {{ $name }}
                @endif
            </h3>

            @if ($category || $location)
                <p class="mt-0.5 truncate text-[12px] text-muted">
                    {{ collect([$category, $location])->filter()->implode(' · ') }}
                </p>
            @endif
        </div>

        <x-badge :tone="$tone['badge']" class="shrink-0">{{ $tone['label'] }}</x-badge>
    </div>

    <div class="flex items-baseline gap-1.5">
        <span class="text-[26px] font-semibold leading-none tabular-nums text-ink">{{ $formatQty($stock) }}</span>
        <span class="text-[12px] font-medium text-muted">{{ $unit }}</span>
        <span class="ml-auto text-[11px] text-muted">Mín. {{ $formatQty($min) }}</span>
    </div>

    <x-progress :value="$progress" :max="100" :tone="$tone['bar']" size="sm" :label="'Existencias de '.$name" />

    <div class="flex flex-wrap items-center gap-2">
        @if ($expiresAt)
            <x-badge tone="warning" icon="calendar-clock">Caduca {{ $expiresAt }}</x-badge>
        @endif

        @if ($price !== null)
            <x-badge tone="outline" icon="tag">{{ \App\Support\Money::format((int) $price) }} ref.</x-badge>
        @endif
    </div>

    @isset($actions)
        <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-border pt-3">{{ $actions }}</div>
    @endisset
</article>
