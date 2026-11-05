@props([
    'name' => 'cantidad',
    'value' => 1,
    'min' => 0,
    'max' => 999,
    'step' => 1,
    'unit' => null,
    'price' => null,
    'label' => 'Cantidad',
])

@php
    $current = (float) $value;
    $price = $price === null ? null : (int) $price;
@endphp

<div
    {{ $attributes->class('space-y-1') }}
    x-data="{
        qty: {{ $current }},
        price: {{ $price ?? 0 }},
        min: {{ (float) $min }},
        max: {{ (float) $max }},
        step: {{ (float) $step }},
        hasPrice: {{ $price === null ? 'false' : 'true' }},
        get subtotal() {
            return Math.round(this.qty * this.price);
        },
        get subtotalLabel() {
            return new Intl.NumberFormat('es-MX', { style: 'currency', currency: 'MXN' }).format(this.subtotal / 100);
        },
        round(value) {
            return Math.round(value * 100) / 100;
        },
        add(delta) {
            this.qty = Math.min(this.max, Math.max(this.min, this.round(this.qty + delta)));
            this.notify();
        },
        notify() {
            this.$dispatch('quantity-change', { name: '{{ $name }}', value: this.qty });
        },
    }"
>
    <div class="inline-flex items-center gap-1 rounded-control border border-border bg-surface p-0.5" role="group" aria-label="{{ $label }}">
        <button
            type="button"
            x-on:click="add(-step)"
            x-bind:disabled="qty <= min"
            aria-label="Quitar cantidad"
            class="inline-flex size-10 items-center justify-center rounded-[6px] text-ink-soft transition-colors hover:bg-canvas disabled:cursor-not-allowed disabled:text-border"
        >
            <x-icon name="minus" class="size-4" />
        </button>

        <input
            type="number"
            name="{{ $name }}"
            x-model.number="qty"
            x-on:input="$nextTick(() => notify())"
            min="{{ $min }}"
            max="{{ $max }}"
            step="{{ $step }}"
            inputmode="decimal"
            aria-label="{{ $label }}"
            class="h-10 w-16 rounded-[6px] border-0 bg-transparent px-1 text-center text-sm font-semibold tabular-nums text-ink focus:ring-2 focus:ring-primary/20 focus:outline-none"
        >

        @if ($unit)
            <span class="pr-1 text-[12px] text-muted">{{ $unit }}</span>
        @endif

        <button
            type="button"
            x-on:click="add(step)"
            x-bind:disabled="qty >= max"
            aria-label="Agregar cantidad"
            class="inline-flex size-10 items-center justify-center rounded-[6px] text-ink-soft transition-colors hover:bg-canvas disabled:cursor-not-allowed disabled:text-border"
        >
            <x-icon name="plus" class="size-4" />
        </button>
    </div>

    @if ($price !== null)
        <p class="text-[11px] tabular-nums text-muted" x-show="hasPrice">
            <span x-text="subtotalLabel">$0.00</span>
        </p>
    @endif
</div>
