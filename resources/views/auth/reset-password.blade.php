<x-layouts.auth title="Crea una nueva contraseña">
    <form method="POST" action="{{ route('password.update') }}" class="space-y-6">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">
        <div class="space-y-2">
            <h1 class="text-[28px] font-semibold leading-tight">Crea una nueva contraseña</h1>
            <p class="text-sm text-muted">Elige una contraseña que no uses en otros servicios.</p>
        </div>
        <x-input label="Correo electrónico" name="email" type="email" :value="$email" required autocomplete="email" />
        <x-input label="Nueva contraseña" name="password" type="password" required autocomplete="new-password" hint="Usa al menos 12 caracteres, una letra y un número." />
        <x-input label="Confirmar contraseña" name="password_confirmation" type="password" required autocomplete="new-password" />
        <x-button type="submit" class="w-full">Guardar contraseña</x-button>
        <a href="{{ route('password.request') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-primary">Solicitar otro enlace</a>
    </form>
</x-layouts.auth>
