<x-card spacing="p-5">
    <h2 class="text-base font-semibold text-ink">Filtrar actividad</h2>
    <form method="GET" action="{{ route('household.index') }}" class="mt-4 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <input type="hidden" name="tab" value="actividad">
        @if ($viewerRole === 'admin')
            <x-select label="Usuario" name="actor">
                <option value="">Todos los integrantes</option>
                @foreach ($members as $member)<option value="{{ $member->id }}" @selected(request('actor') == $member->id)>{{ $member->name }}</option>@endforeach
            </x-select>
        @endif
        <x-input label="Desde" name="from" type="date" :value="request('from')" />
        <x-input label="Hasta" name="to" type="date" :value="request('to')" />
        <div class="flex items-end gap-2"><x-button type="submit">Aplicar filtros</x-button><x-button variant="secondary" href="{{ route('household.index', ['tab' => 'actividad']) }}">Limpiar</x-button></div>
    </form>
</x-card>

<x-banner tone="info" title="Solo ves actividad a la que tienes acceso">Los registros personales sin permiso no aparecen en este historial, ni sus nombres, importes o detalles.</x-banner>

<x-card spacing="p-5">
    <h2 class="text-base font-semibold text-ink">Actividad reciente</h2>
    @if ($activityLogs->isEmpty())
        <x-empty-state icon="history" title="No hay actividad visible" description="Cuando haya acciones del hogar que puedas consultar, aparecerán aquí." />
    @else
        <ul class="divide-y divide-border">
            @foreach ($activityLogs as $log)
                @php
                    $actor = $log->actor?->name ?? 'Sistema';
                    $iniciales = collect(explode(' ', $actor))->map(fn ($word) => mb_substr($word, 0, 1))->take(2)->implode('');
                    $accion = $log->label();
                @endphp
                <li class="py-4"><div class="flex items-center gap-3"><span class="flex size-11 items-center justify-center rounded-full bg-info-soft text-sm font-semibold text-info">{{ strtoupper($iniciales) }}</span><div><p class="text-sm font-semibold text-ink">{{ $actor }}</p><p class="text-[12px] text-muted">{{ $log->created_at->timezone($household->timezone)->translatedFormat('j M Y · H:i') }}</p></div></div><p class="mt-3 text-sm font-semibold text-ink">{{ $accion }}</p><p class="mt-1 text-[13px] text-muted">Mi hogar · Registro de auditoría #{{ $log->id }}</p></li>
            @endforeach
        </ul>
    @endif
</x-card>
