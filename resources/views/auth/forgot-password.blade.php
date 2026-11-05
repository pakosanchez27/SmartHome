<x-layouts.auth title="Recupera tu acceso">
    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf
        <div class="space-y-2">
            <h1 class="text-[28px] font-semibold leading-tight">Recupera tu acceso</h1>
            <p class="text-sm text-muted">Te enviaremos un enlace para crear una nueva contraseña.</p>
        </div>
        @if (session('status'))
            <x-banner tone="success">{{ session('status') }}</x-banner>
        @endif
        <x-input label="Correo electrónico" name="email" type="email" required autocomplete="email" maxlength="255" />
        <x-button type="submit" class="w-full">Enviar enlace</x-button>
        <a href="{{ route('login') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-primary">Volver a iniciar sesión</a>
    </form>
</x-layouts.auth>
