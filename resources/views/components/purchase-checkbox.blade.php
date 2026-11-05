@props([
    'name',
    'label',
    'description' => null,
    'checked' => false,
    'quantity' => null,
    'unit' => null,
    'price' => null,
])

@php
    $id = $attributes->get('id') ?? 'compra-'.trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name), '-');
    $price = $price === null ? null : (int) $price;
    $total = $price !== null && $quantity !== null ? (int) round($price * (float) $quantity) : $price;
@endphp

<label
    for="{{ $id }}"
    x-data="{ bought: @js((bool) $checked) }"
    x-bind:class="bought ? 'border-success/30 bg-success-soft' : 'border-border bg-surface hover:bg-canvas'"
    {{ $attributes->class('flex min-h-14 cursor-pointer items-start gap-3 rounded-card border p-3 transition-colors') }}
>
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="checkbox"
        value="1"
        x-model="bought"
        @checked($checked)
        class="mt-1 size-5 shrink-0 cursor-pointer rounded border-border accent-primary focus:ring-2 focus:ring-primary/20"
    >

    <span class="min-w-0 flex-1 space-y-0.5">
        <span class="block text-sm font-medium leading-snug text-ink" x-bind:class="bought ? 'text-muted line-through' : ''">{{ $label }}</span>

        @if ($description)
            <span class="block text-[12px] leading-relaxed text-muted">{{ $description }}</span>
        @endif

        @if ($quantity !== null)
            <span class="block text-[11px] uppercase tracking-wide text-muted">
                {{ $quantity }}@if ($unit) {{ $unit }}@endif
                @if ($price !== null)
                    <span aria-hidden="true">·</span> {{ \App\Support\Money::format($price) }} c/u
                @endif
            </span>
        @endif
    </span>

    <span class="shrink-0 text-right">
        @if ($total !== null)
            <span class="block text-sm font-semibold tabular-nums text-ink">{{ \App\Support\Money::format($total) }}</span>
        @endif

        <span
            class="mt-1 inline-flex items-center rounded-full px-2 py-1 text-[11px] font-medium leading-none"
            x-bind:class="bought ? 'bg-success-soft text-success' : 'bg-warning-soft text-warning'"
        >
            <span x-text="bought ? 'Comprado' : 'Pendiente'">{{ $checked ? 'Comprado' : 'Pendiente' }}</span>
        </span>
    </span>
</label>
