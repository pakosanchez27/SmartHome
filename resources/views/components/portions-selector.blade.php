@props([
    'name' => 'porciones',
    'base' => 2,
    'value' => null,
    'min' => 1,
    'max' => 24,
    'label' => 'Porciones',
    'unit' => 'porciones',
])

@php
    $base = max(1, (int) $base);
    $current = max((int) $min, min((int) $max, (int) ($value ?? $base)));
@endphp

<div
    {{ $attributes->class('space-y-2') }}
    x-data="{
        base: {{ $base }},
        qty: {{ $current }},
        min: {{ (int) $min }},
        max: {{ (int) $max }},
        get factor() {
            return Math.round((this.qty / this.base) * 100) / 100;
        },
        add(delta) {
            this.qty = Math.min(this.max, Math.max(this.min, this.qty + delta));
        },
    }"
>
    <div class="flex flex-wrap items-center justify-between gap-2">
        <span class="text-[13px] font-medium text-ink">{{ $label }}</span>

        <span class="inline-flex items-center gap-1.5 rounded-full bg-primary-soft px-2.5 py-1 text-[12px] font-semibold text-primary">
            ×<span x-text="factor">{{ number_format($current / $base, 2, '.', '') }}</span>
        </span>
    </div>

    <div class="flex flex-wrap items-center gap-3">
        <div class="inline-flex items-center gap-1 rounded-control border border-border bg-surface p-1" role="group" aria-label="{{ $label }}">
            <button
                type="button"
                x-on:click="add(-1)"
                x-bind:disabled="qty <= min"
                aria-label="Quitar una porción"
                class="inline-flex size-11 items-center justify-center rounded-[6px] text-ink-soft transition-colors hover:bg-canvas disabled:cursor-not-allowed disabled:text-border"
            >
                <x-icon name="minus" class="size-[18px]" />
            </button>

            <span class="min-w-12 text-center text-sm font-semibold tabular-nums text-ink" aria-live="polite">
                <span x-text="qty">{{ $current }}</span>
                <span class="ml-0.5 text-[12px] font-normal text-muted">{{ $unit }}</span>
            </span>

            <button
                type="button"
                x-on:click="add(1)"
                x-bind:disabled="qty >= max"
                aria-label="Agregar una porción"
                class="inline-flex size-11 items-center justify-center rounded-[6px] text-ink-soft transition-colors hover:bg-canvas disabled:cursor-not-allowed disabled:text-border"
            >
                <x-icon name="plus" class="size-[18px]" />
            </button>
        </div>

        <p class="text-[12px] leading-relaxed text-muted">
            Receta base para {{ $base }} {{ $unit }}.
        </p>
    </div>

    <p class="text-[12px] leading-relaxed text-muted">
        Los ingredientes se escalan automáticamente; planear no descuenta despensa.
    </p>

    <input type="hidden" name="{{ $name }}" x-bind:value="qty">
</div>
