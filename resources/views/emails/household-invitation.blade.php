@component('mail::message')
# Has recibido una invitación a SmartHome

**{{ $invitation->invitedBy->name }}** te invitó al hogar **{{ $invitation->household->name }}**
con el rol de **{{ $invitation->roleLabel() }}**.

@if ($invitation->message)
> {{ $invitation->message }}
@endif

En SmartHome su hogar puede administrar juntos las finanzas, la despensa,
las compras y las comidas de la casa. Cada persona decide qué comparte:
las cuentas personales siguen siendo privadas aunque integres el hogar.

**Rol {{ $invitation->roleLabel() }}:** consulta lo compartido; no edita ni invita.
Este enlace vence el {{ $invitation->expires_at->translatedFormat('d de F de Y') }}.

@component('mail::button', ['url' => $url])
Revisar la invitación
@endcomponent

Si no esperabas esta invitación, ignorar este correo no causa ninguna consecuencia.
@endcomponent
