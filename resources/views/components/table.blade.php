@props([
    'caption' => null,
    'emptyTitle' => 'Aún no hay registros',
    'emptyDescription' => null,
    'emptyIcon' => 'list',
])

{{--
    Tabla de escritorio con contraparte móvil obligatoria.
    Uso:
        <x-table caption="Movimientos">
            <x-slot:head><th class="table-th">Concepto</th></x-slot:head>
            <tr>…</tr>
            <x-slot:mobile>
                <x-list-row … />
            </x-slot:mobile>
        </x-table>

    La tabla se oculta por debajo de 768 px para evitar scroll horizontal.
--}}

@php
    $hasRows = ! $slot->isEmpty();
    $hasMobile = isset($mobile) && $mobile->isNotEmpty();
@endphp

<div {{ $attributes->class('space-y-3') }}>
    <div class="hidden overflow-x-auto rounded-card border border-border bg-surface shadow-card md:block">
        <table class="w-full text-left text-sm">
            @if ($caption)
                <caption class="sr-only">{{ $caption }}</caption>
            @endif

            <thead class="border-b border-border bg-canvas/70">
                <tr>{{ $head ?? '' }}</tr>
            </thead>

            <tbody class="divide-y divide-border">
                @if ($hasRows)
                    {{ $slot }}
                @else
                    <tr>
                        <td colspan="12" class="px-4 py-6">
                            <x-empty-state :icon="$emptyIcon" :title="$emptyTitle" :description="$emptyDescription" compact />
                        </td>
                    </tr>
                @endif
            </tbody>
        </table>
    </div>

    <div class="space-y-2 md:hidden">
        @if (! $hasRows)
            <x-card>
                <x-empty-state :icon="$emptyIcon" :title="$emptyTitle" :description="$emptyDescription" compact />
            </x-card>
        @elseif ($hasMobile)
            {{ $mobile }}
        @else
            <x-banner tone="warning" title="Falta la vista móvil de esta tabla">
                Cada tabla debe declarar su contraparte en lista con <code class="rounded bg-surface px-1 py-0.5">&lt;x-slot:mobile&gt;</code>.
            </x-banner>
        @endif
    </div>
</div>
