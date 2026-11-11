@php
    /** @var $transaction \App\Models\Transaction */
    /** @var $amount int */
    $esIngreso = $transaction->type->value === 'income';
    $anulado = $transaction->isCanceled();
@endphp

<x-layouts.app
    title="{{ $transaction->description }}"
    :breadcrumbs="[
        ['label' => 'Finanzas', 'url' => route('finance.index')],
        ['label' => 'Movimientos', 'url' => route('finance.transactions.index')],
        ['label' => $transaction->description],
    ]"
    :demo="false"
>
    <x-page-header
        eyebrow="{{ $transaction->type->label() }}"
        title="{{ $transaction->description }}"
        description="Registrado por {{ $transaction->creator?->name ?? 'un integrante' }} el {{ $transaction->created_at->translatedFormat('j M Y') }}."
    >
        <x-slot:actions>
            @if ($canEdit && ! $anulado)
                <x-button variant="secondary" href="{{ route('finance.transactions.edit', $transaction) }}">
                    <x-icon name="notebook-pen" class="size-[18px]" />
                    Corregir datos
                </x-button>
                <x-button variant="destructive" x-on:click="$dispatch('open-modal', 'anular-movimiento')">
                    <x-icon name="ban" class="size-[18px]" />
                    Anular
                </x-button>
            @endif
        </x-slot:actions>
    </x-page-header>

    @if (session('status'))
        <x-banner tone="success">{{ session('status') }}</x-banner>
    @endif

    @if ($anulado)
        <x-banner tone="danger" title="Movimiento anulado">
            Sigue en el historial con fines de auditoría, pero ya no cuenta en saldos ni totales. El actor de la anulación quedó registrado.
        </x-banner>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
        <x-card class="space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <p class="text-[13px] text-muted">{{ $transaction->type->label() }} · importe registrado</p>
                    <p class="text-[32px] font-semibold leading-tight tabular-nums {{ $esIngreso ? 'text-success' : 'text-ink' }}">
                        {{ \App\Support\Money::format($esIngreso ? $amount : -$amount) }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-2">
                    <x-badge :tone="$transaction->status->tone()" icon="{{ $transaction->type->value === 'income' ? 'trending-up' : ($transaction->type->value === 'expense' ? 'trending-down' : 'arrow-left-right') }}">
                        {{ $transaction->status->label() }}
                    </x-badge>
                    <x-badge tone="{{ $transaction->isShared() || $transaction->owner_user_id === null ? 'outline' : 'neutral' }}">
                        {{ $transaction->owner_user_id === null ? 'Del hogar' : ($transaction->isShared() ? 'Personal compartido' : 'Personal') }}
                    </x-badge>
                </div>
            </div>

            <dl class="grid gap-4 border-t border-border pt-5 sm:grid-cols-2">
                <div>
                    <dt class="text-[12px] text-muted">Fecha</dt>
                    <dd class="text-sm font-medium text-ink">{{ $transaction->occurred_on->translatedFormat('l j F Y') }}</dd>
                </div>

                <div>
                    <dt class="text-[12px] text-muted">Categoría</dt>
                    <dd class="text-sm font-medium text-ink">{{ $transaction->category?->name ?? 'Sin categoría' }}</dd>
                </div>

                @foreach ($transaction->lines as $line)
                    <div>
                        <dt class="text-[12px] text-muted">
                            {{ $line->direction->label() }} ·
                            {{ $transaction->type->isTransfer() ? ($line->direction->value === 'out' ? 'cuenta de origen' : 'cuenta de destino') : 'cuenta' }}
                        </dt>
                        <dd class="text-sm font-medium text-ink">
                            {{ $line->account?->name ?? 'Cuenta eliminada' }}
                            <span class="tabular-nums text-muted">({{ \App\Support\Money::format($line->signedCents()) }})</span>
                        </dd>
                    </div>
                @endforeach
            </dl>

            @if ($transaction->notes)
                <div class="border-t border-border pt-5">
                    <p class="text-[12px] text-muted">Notas</p>
                    <p class="whitespace-pre-line text-sm leading-relaxed text-ink-soft">{{ $transaction->notes }}</p>
                </div>
            @endif
        </x-card>

        <aside class="space-y-4">
            <x-banner tone="info" title="Cómo afecta tu saldo">
                @if ($transaction->status->value === 'confirmed')
                    Este movimiento está confirmado: ya está considerado en los saldos de las cuentas involucradas.
                @elseif ($transaction->status->value === 'pending')
                    Está pendiente: no modifica saldos hasta que se confirme.
                @else
                    Está anulado: no modifica saldos.
                @endif
            </x-banner>

            @if ($canEdit && ! $anulado)
                <x-banner tone="warning" title="Para cambiar importe o cuenta">
                    Anula este movimiento y registra uno nuevo: así el historial siempre explica cada cambio de saldo.
                </x-banner>
            @endif
        </aside>
    </div>

    @if ($canEdit && ! $anulado)
        <x-modal
            name="anular-movimiento"
            title="¿Anular este movimiento?"
            description="Dejará de contar en saldos y totales, pero se conserva en el historial con la fecha y la persona que lo anuló."
            size="sm"
        >
            <x-banner tone="warning" title="Esta acción no se puede deshacer desde la interfaz">
                Para corregir un importe, anula y vuelve a registrar el movimiento correcto.
            </x-banner>

            <form method="POST" action="{{ route('finance.transactions.annul', $transaction) }}">
                @csrf
                <div class="flex flex-col-reverse gap-2 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" type="button" x-on:click="$dispatch('close-modal', 'anular-movimiento')">Volver sin anular</x-button>
                    <x-button variant="destructive" type="submit">Anular movimiento</x-button>
                </div>
            </form>
        </x-modal>
    @endif
</x-layouts.app>
