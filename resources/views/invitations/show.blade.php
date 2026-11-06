<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invitación al hogar · SmartHome</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-dvh bg-canvas text-ink">
    <header class="hidden h-24 items-center gap-2.5 px-12 sm:flex">
        <img src="{{ asset('auth-brand.svg') }}" width="28" height="28" alt="">
        <span class="text-[21px] font-semibold">SmartHome</span>
        <span class="text-[13px] text-muted">Un hogar, un equipo</span>
    </header>
    <main class="mx-auto flex w-full max-w-[528px] flex-col items-center gap-8 px-6 pb-12 pt-14 sm:pt-24">
        <div class="flex items-center gap-2.5 sm:hidden">
            <img src="{{ asset('auth-brand.svg') }}" width="28" height="28" alt="">
            <span class="text-[21px] font-semibold">SmartHome</span>
        </div>
        <div class="w-full sm:rounded-2xl sm:border sm:border-border sm:bg-surface sm:p-8">
            @if ($invitation === null)
                <div class="space-y-4">
                    <h1 class="text-[28px] font-semibold leading-tight">Esta invitación ya no existe</h1>
                    <p class="text-sm text-muted">El enlace es inválido. Pide a quien te invitó que envíe una nueva desde su módulo «Mi hogar».</p>
                    <a href="{{ route('home') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-primary">Ir al inicio</a>
                </div>
            @else
                <div class="space-y-2">
                    <h1 class="text-[28px] font-semibold leading-tight">
                        {{ $state === 'pending' ? 'Revisa tu invitación' : match ($state) {
                            'expired' => 'Invitación vencida',
                            'accepted' => 'Invitación ya usada',
                            'rejected' => 'Invitación rechazada',
                            'revoked' => 'Invitación cancelada',
                            default => 'Invitación al hogar',
                        } }}
                    </h1>
                    <p class="text-sm text-muted">
                        {{ $invitation->invitedBy->name }} te invitó a su hogar en SmartHome.
                    </p>
                </div>

                <div class="mt-4 space-y-1 rounded-control border border-border bg-surface-muted px-3 py-2.5 text-sm">
                    <p class="text-muted">Hogar</p>
                    <p class="font-semibold text-ink">{{ $invitation->household->name }}</p>
                    <p class="mt-2 text-muted">Rol que recibirás</p>
                    <p class="font-semibold text-ink">{{ $invitation->roleLabel() }} — consulta lo compartido, sin editar ni invitar.</p>
                    <p class="mt-2 text-muted">Enlace válido hasta</p>
                    <p class="font-semibold text-ink">{{ $invitation->expires_at->translatedFormat('j de F de Y') }}</p>
                </div>

                @if ($state === 'pending')
                    <x-banner tone="info">
                        Las cuentas personales de cada integrante permanecen privadas: entrar al hogar solo te da acceso a lo compartido.
                    </x-banner>
                @else
                    <x-banner tone="warning">
                        {{ match ($state) {
                            'expired' => 'Esta invitación venció el '.$invitation->expires_at->translatedFormat('j M Y').'. Pide una nueva por correo.',
                            'accepted' => 'Esta invitación ya fue usada. Inicia sesión con tu cuenta para entrar al hogar.',
                            'rejected' => 'Esta invitación fue rechazada.',
                            default => 'Esta invitación ya no está disponible.',
                        } }}
                    </x-banner>
                @endif

                @if ($state === 'pending')
                    @php $correoCoincide = \Illuminate\Support\Facades\Auth::check() && strcasecmp(\Illuminate\Support\Facades\Auth::user()->email, $invitation->email) === 0; @endphp

                    @if (\Illuminate\Support\Facades\Auth::check())
                        @if ($correoCoincide)
                            <form method="POST" action="{{ route('invitation.accept', $token) }}" class="mt-4 space-y-3">
                                @csrf
                                <x-button type="submit" class="w-full">Aceptar e integrarme al hogar</x-button>
                            </form>
                        @else
                            <x-banner tone="warning">
                                Tu sesión usa otro correo; esta invitación se envió a {{ $invitation->email }}. Cierra sesión e inicia con ese correo, o pide una invitación a tu cuenta.
                            </x-banner>
                        @endif

                        <form method="POST" action="{{ route('invitation.reject', $token) }}" class="mt-2">
                            @csrf
                            <x-button variant="ghost" type="submit" class="w-full">Rechazar invitación</x-button>
                        </form>
                        <p class="mt-2 text-center text-xs text-muted">Rechazar no elimina tu cuenta; solo anula este acceso.</p>
                    @else
                        {{-- Puente: no existe frame del lado del invitado en Figma; se pidió diseño oficial (grupo A). --}}
                        <div class="mt-4 space-y-3">
                            <x-button class="w-full" href="{{ route('login') }}">Inicia sesión con {{ $invitation->email }}</x-button>
                            <p class="text-center text-sm text-muted">
                                ¿Aún no tienes cuenta?
                                <a href="{{ route('household.register') }}" class="font-semibold text-primary">Crea tu cuenta con ese correo</a>
                                y el enlace cerrará tu invitación al terminar de registrarte.
                            </p>
                            <form method="POST" action="{{ route('invitation.reject', $token) }}">
                                @csrf
                                <x-button variant="ghost" type="submit" class="w-full">Rechazar sin registrarme</x-button>
                            </form>
                        </div>
                    @endif
                @endif
            @endif
        </div>
        <p class="text-xs text-muted">SmartHome</p>
    </main>
</body>
</html>
