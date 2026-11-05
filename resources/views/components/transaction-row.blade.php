@props([
    'concept',
    'amount',
    'type' => 'expense',
    'account' => null,
    'date' => null,
    'category' => null,
    'status' => 'confirmed',
    'href' => null,
])

@php
    $types = [
        'expense' => ['icon' => 'trending-down', 'label' => 'Gasto', 'tone' => 'danger', 'sign' => -1],
        'income' => ['icon' => 'trending-up', 'label' => 'Ingreso', 'tone' => 'success', 'sign' => 1],
        'transfer' => ['icon' => 'arrow-left-right', 'label' => 'Transferencia', 'tone' => 'info', 'sign' => 1],
    ];

    $statuses = [
        'confirmed' => ['label' => 'Registrado', 'tone' => 'success'],
        'pending' => ['label' => 'Pendiente', 'tone' => 'warning'],
        'canceled' => ['label' => 'Anulado', 'tone' => 'danger'],
    ];

    $meta = $types[$type] ?? $types['expense'];
    $state = $statuses[$status] ?? $statuses['confirmed'];

    $absolute = abs((int) $amount);
    $signed = $meta['sign'] < 0 ? -$absolute : $absolute;

    $toneClasses = [
        'danger' => 'bg-danger-soft text-danger',
        'success' => 'bg-success-soft text-success',
        'info' => 'bg-info-soft text-info',
    ];

    $tag = $href ? 'a' : 'div';
@endphp

<{{ $tag }}
    @if ($href) href="{{ $href }}" @endif
    {{ $attributes->class(['flex min-h-14 items-center gap-3 rounded-control px-2 py-2.5 transition-colors', 'hover:bg-canvas' => $href]) }}
>
    <span class="flex size-10 shrink-0 items-center justify-center rounded-control {{ $toneClasses[$meta['tone']] }}">
        <x-icon :name="$meta['icon']" class="size-[18px]" />
    </span>

    <span class="min-w-0 flex-1 space-y-0.5">
        <span class="block truncate text-sm font-medium text-ink">{{ $concept }}</span>
        <span class="block truncate text-[12px] text-muted">
            {{ collect([$meta['label'], $date, $account, $category])->filter()->implode(' · ') }}
        </span>
    </span>

    <span class="shrink-0 text-right">
        <span @class([
            'block text-sm font-semibold tabular-nums',
            'text-success' => $type === 'income',
            'text-ink' => $type === 'expense',
            'text-info' => $type === 'transfer',
        ])>{{ \App\Support\Money::format($signed) }}</span>

        <x-badge :tone="$state['tone']" class="mt-1">{{ $state['label'] }}</x-badge>
    </span>
</{{ $tag }}>
