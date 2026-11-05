@props([
    'name' => 'cantidad',
    'value' => 1,
    'min' => 1,
    'max' => 99,
    'step' => 1,
    'unit' => null,
    'label' => null,
])

<div {{ $attributes->only('class')->class('space-y-1.5') }}>
    @if ($label)
        <span class="block text-[13px] font-medium text-ink">{{ $label }}</span>
    @endif

    <div
        x-data="{ qty: {{ (int) $value }} }"
        class="inline-flex items-center gap-1 rounded-control border border-border bg-surface p-1"
        role="group"
        @if ($label) aria-label="{{ $label }}" @endif
    >
        <button
            type="button"
            x-on:click="qty = Math.max({{ (int) $min }}, qty - {{ (int) $step }})"
            x-bind:disabled="qty <= {{ (int) $min }}"
            aria-label="Quitar una unidad"
            class="inline-flex size-11 items-center justify-center rounded-[6px] text-ink-soft transition-colors hover:bg-canvas disabled:cursor-not-allowed disabled:text-border"
        >
            <x-icon name="minus" class="size-[18px]" />
        </button>

        <span class="min-w-10 text-center text-sm font-semibold tabular-nums text-ink" aria-live="polite">
            <span x-text="qty">{{ (int) $value }}</span>@if ($unit)<span class="ml-0.5 text-[12px] font-normal text-muted">{{ $unit }}</span>@endif
        </span>

        <button
            type="button"
            x-on:click="qty = Math.min({{ (int) $max }}, qty + {{ (int) $step }})"
            x-bind:disabled="qty >= {{ (int) $max }}"
            aria-label="Agregar una unidad"
            class="inline-flex size-11 items-center justify-center rounded-[6px] text-ink-soft transition-colors hover:bg-canvas disabled:cursor-not-allowed disabled:text-border"
        >
            <x-icon name="plus" class="size-[18px]" />
        </button>

        <input type="hidden" name="{{ $name }}" x-bind:value="qty">
    </div>
</div>
