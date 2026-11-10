<x-banner tone="info" title="Administrar no da acceso a lo privado">La administración puede gestionar el hogar, integrantes e invitaciones, pero nunca ve cuentas, saldos ni registros personales de otras personas.</x-banner>

<x-card spacing="p-5">
    <h2 class="text-base font-semibold text-ink">Qué puede hacer cada rol</h2>
    <p class="mt-1 text-[13px] text-muted">El hogar tiene {{ $members->count() }} {{ $members->count() === 1 ? 'integrante activo' : 'integrantes activos' }}.</p>
    @php $roles = [
        'Administradora' => ['Organiza el acceso del hogar.', 'Compartir', 'Ver y editar', 'Crear, editar y anular', 'Invitar, cambiar rol y retirar', 'Ver toda autorizada'],
        'Integrante' => ['Colabora en el día a día.', 'Ver y editar', 'Crear, editar y anular', 'Ver registros autorizados', 'Ver integrantes, no gestionar', 'Solo datos autorizados'],
        'Consulta' => ['Se mantiene al tanto, sin editar.', 'Solo ver', 'Solo registros autorizados', 'Sin edición ni anulación', 'Ver integrantes, no gestionar', 'Consultar datos autorizados'],
    ]; @endphp
    <div class="mt-4 grid gap-4 lg:grid-cols-3">
        @foreach ($roles as $rol => [$descripcion, $compras, $finanzas, $hogar, $integrantes, $acceso])
            <div class="rounded-card border border-border p-4"><h3 class="font-semibold text-ink">{{ $rol }}</h3><p class="mt-1 text-[13px] text-muted">{{ $descripcion }}</p><dl class="mt-3 space-y-2 text-[12px]"><div class="border-t border-border pt-2"><dt class="text-muted">Compras</dt><dd class="font-medium text-ink">{{ $compras }}</dd></div><div><dt class="text-muted">Finanzas</dt><dd class="font-medium text-ink">{{ $finanzas }}</dd></div><div><dt class="text-muted">Gestión de hogar</dt><dd class="font-medium text-ink">{{ $hogar }}</dd></div><div><dt class="text-muted">Integrantes</dt><dd class="font-medium text-ink">{{ $integrantes }}</dd></div><div><dt class="text-muted">Acceso</dt><dd class="font-medium text-ink">{{ $acceso }}</dd></div></dl></div>
        @endforeach
    </div>
</x-card>

<x-card spacing="p-5">
    <h2 class="text-base font-semibold text-ink">Tus cuentas personales</h2>
    <p class="mt-1 text-[13px] text-muted">Aquí podrás consultar la información personal cuando registres cuentas y movimientos financieros. Nadie más verá tus saldos, ni siquiera la administración.</p>
    <x-button variant="secondary" class="mt-4" disabled>Revisar antes de compartir</x-button>
    <p class="mt-3 text-[12px] text-muted">Compartir una cuenta requiere confirmación. No cambia tu rol en el hogar.</p>
</x-card>

<x-card spacing="p-5">
    <h2 class="text-base font-semibold text-ink">Acceso del asistente IA</h2>
    <x-badge tone="primary">Solo información autorizada</x-badge>
    <p class="mt-3 text-[13px] text-muted">El asistente respeta los mismos permisos que tú: no ve cuentas personales de otra persona ni puede crear, cambiar o confirmar datos sin tu revisión.</p>
</x-card>

<x-banner tone="success" title="Un cambio de rol queda en Actividad">Cada cambio de rol, invitación o retiro se registra con su autora y fecha para conservar trazabilidad.</x-banner>
