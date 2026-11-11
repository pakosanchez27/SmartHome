@php
    /** @var $transaction \App\Models\Transaction */
    /** @var $categories \Illuminate\Support\Collection<\App\Models\Category> */
    $esTransferencia = $transaction->type->isTransfer();
    $visibilidadActual = old(
        'visibility',
        $transaction->owner_user_id === null ? 'shared' : 'private'
    );
@endphp

<x-layouts.app
    title="Corregir movimiento"
    :breadcrumbs="[
        ['label' => 'Finanzas', 'url' => route('finance.index')],
        ['label' => 'Movimientos', 'url' => route('finance.transactions.index')],
        ['label' => $transaction->description, 'url' => route('finance.transactions.show', $transaction)],
        ['label' => 'Corregir'],
    ]"
    :demo="false"
>
    <x-page-header
        eyebrow="{{ $transaction->type->label() }} · {{ $transaction->status->label() }}"
        title="Corregir «{{ $transaction->description }}»"
        description="Aquí solo se corrigen los datos descriptivos. El importe y las cuentas no cambian para que el saldo siempre tenga explicación."
    />

    <div class="grid gap-4 lg:grid-cols-[minmax(0,1fr)_320px]">
        <x-card class="space-y-5">
            <form method="POST" action="{{ route('finance.transactions.update', $transaction) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <x-input
                    label="Concepto"
                    name="description"
                    value="{{ old('description', $transaction->description) }}"
                    required
                    maxlength="120"
                />

                <div class="grid gap-4 sm:grid-cols-2">
                    <x-date-picker
                        label="Fecha"
                        name="occurred_on"
                        :value="old('occurred_on', $transaction->occurred_on->toDateString())"
                        :max="now()->toDateString()"
                        quickToday
                        required
                    />

                    @if (! $esTransferencia)
                        <x-select label="Categoría" name="category_id" :value="old('category_id', $transaction->category_id)" required>
                            <option value="">Elige una categoría</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}" @selected(old('category_id', $transaction->category_id) == $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </x-select>
                    @endif
                </div>

                <x-textarea label="Notas (opcional)" name="notes" maxlength="500">{{ old('notes', $transaction->notes) }}</x-textarea>

                <fieldset class="space-y-2">
                    <legend class="text-[13px] font-medium text-ink">Visibilidad <span class="text-danger" aria-hidden="true">*</span></legend>

                    <div class="grid gap-2 sm:grid-cols-2">
                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-control border border-border bg-surface p-3.5 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                            <input type="radio" name="visibility" value="shared" class="mt-0.5 size-4 accent-[#0F766E]" @checked($visibilidadActual === 'shared') required>
                            <span class="space-y-0.5">
                                <span class="block text-sm font-medium text-ink">Compartido con el hogar</span>
                                <span class="block text-[12px] leading-relaxed text-muted">Visible en listados y totales de todos.</span>
                            </span>
                        </label>

                        <label class="flex min-h-11 cursor-pointer items-start gap-3 rounded-control border border-border bg-surface p-3.5 transition-colors has-[:checked]:border-primary has-[:checked]:bg-primary-soft">
                            <input type="radio" name="visibility" value="private" class="mt-0.5 size-4 accent-[#0F766E]" @checked($visibilidadActual === 'private')>
                            <span class="space-y-0.5">
                                <span class="block text-sm font-medium text-ink">Solo para mí</span>
                                <span class="block text-[12px] leading-relaxed text-muted">Nadie más lo verá, ni en listados ni en totales.</span>
                            </span>
                        </label>
                    </div>

                    @if ($errors->has('visibility'))
                        <p class="text-[12px] font-medium leading-relaxed text-danger">{{ $errors->first('visibility') }}</p>
                    @endif
                </fieldset>

                <div class="flex flex-col-reverse gap-2 border-t border-border pt-5 sm:flex-row sm:justify-end">
                    <x-button variant="secondary" type="button" href="{{ route('finance.transactions.show', $transaction) }}">Cancelar</x-button>
                    <x-button type="submit">Guardar corrección</x-button>
                </div>
            </form>
        </x-card>

        <aside class="space-y-4">
            <x-banner tone="warning" title="¿Importe o cuenta equivocados?">
                Anula este movimiento y registra el correcto. Así cada cambio de saldo queda explicado en el historial (RG-10).
            </x-banner>

            <x-card padding="p-4">
                <p class="text-[12px] text-muted">Importe actual (no editable aquí)</p>
                <p class="text-[22px] font-semibold tabular-nums text-ink">
                    {{ \App\Support\Money::format($transaction->amountCents()) }}
                </p>
                <p class="mt-1 text-[12px] text-muted">
                    @foreach ($transaction->lines as $line)
                        {{ $line->direction->label() }}: {{ $line->account?->name }}@if (! $loop->last) · @endif
                    @endforeach
                </p>
            </x-card>
        </aside>
    </div>
</x-layouts.app>
