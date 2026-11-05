@props([
    'label',
    'planned',
    'spent',
    'hint' => null,
])

@php
    $planned = max(0, (int) $planned);
    $spent = max(0, (int) $spent);

    $rawPercent = $planned > 0 ? (int) round(($spent / $planned) * 100) : 0;
    $barPercent = max(0, min(100, $rawPercent));
    $remaining = $planned - $spent;

    $tone = match (true) {
        $spent > $planned => 'danger',
        $rawPercent >= 80 => 'warning',
        default => 'primary',
    };

    $barTones = [
        'primary' => 'bg-primary',
        'warning' => 'bg-warning',
        'danger' => 'bg-danger',
    ];
@endphp

<div {{ $attributes->class('space-y-2') }}>
    <div class="flex items-baseline justify-between gap-3">
        <span class="text-[13px] font-medium text-ink-soft">{{ $label }}</span>

        <span @class([
            'text-[12px] font-semibold tabular-nums',
            'text-danger' => $spent > $planned,
            'text-warning' => $spent <= $planned && $rawPercent >= 80,
            'text-muted' => $spent <= $planned && $rawPercent < 80,
        ])>{{ $rawPercent }}%</span>
    </div>

    <div
        class="h-2.5 w-full overflow-hidden rounded-full bg-border"
        role="progressbar"
        aria-valuemin="0"
        aria-valuemax="100"
        aria-valuenow="{{ $barPercent }}"
        aria-label="{{ $label }}: {{ $rawPercent }}% utilizado"
    >
        <div class="h-full rounded-full transition-[width] {{ $barTones[$tone] }}" style="width: {{ $barPercent }}%"></div>
    </div>

    <div class="flex flex-wrap items-baseline justify-between gap-x-3 gap-y-1 text-[12px]">
        <span class="tabular-nums text-muted">
            <span class="font-medium text-ink">{{ \App\Support\Money::format($spent) }}</span>
            de {{ \App\Support\Money::format($planned) }}
        </span>

        @if ($spent > $planned)
            <span class="font-medium tabular-nums text-danger">
                Excedido {{ \App\Support\Money::format($spent - $planned) }}
            </span>
        @else
            <span class="tabular-nums text-muted">
                Restan {{ \App\Support\Money::format($remaining) }}
            </span>
        @endif
    </div>

    @if ($hint)
        <p class="text-[12px] leading-relaxed text-muted">{{ $hint }}</p>
    @endif
</div>
