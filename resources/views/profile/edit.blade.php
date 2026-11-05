<x-layouts.app title="Mi perfil" :demo="false" :breadcrumbs="[['label' => 'Mi perfil']]">
    <x-page-header title="Mi perfil" description="Tu información y tus preferencias, en un solo lugar." />

    @if (session('status'))
        <x-banner tone="success">{{ session('status') }}</x-banner>
    @endif

    <div class="grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(0,1fr)]">
        <div class="space-y-4">
            <x-card class="space-y-4">
                <x-section-heading title="Información personal" />
                <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                    @csrf
                    @method('PATCH')
                    <x-input label="Nombre" name="name" :value="old('name', $user->name)" required maxlength="255" autocomplete="name" />
                    <div class="space-y-1.5">
                        <label for="profile-email" class="block text-[13px] font-medium text-ink">Correo electrónico</label>
                        <input id="profile-email" type="email" value="{{ $user->email }}" readonly class="h-11 w-full rounded-control border border-border bg-surface-muted px-3.5 text-sm text-ink">
                        <p class="text-xs text-muted">El cambio de correo aún no está disponible.</p>
                    </div>
                    <x-button type="submit">Guardar perfil</x-button>
                </form>
            </x-card>

            <x-card class="space-y-4">
                <x-section-heading title="Cambiar contraseña" description="Usa una contraseña única. No cambiaremos nada hasta que confirmes." />
                <form method="POST" action="{{ route('profile.password') }}" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <x-input label="Contraseña actual" name="current_password" type="password" required autocomplete="current-password" :error="$errors->password->first('current_password')" />
                    <x-input label="Nueva contraseña" name="password" type="password" required autocomplete="new-password" hint="Usa al menos 12 caracteres, una letra y un número." :error="$errors->password->first('password')" />
                    <x-input label="Confirmar nueva contraseña" name="password_confirmation" type="password" required autocomplete="new-password" />
                    <x-button type="submit" variant="secondary">Actualizar contraseña</x-button>
                </form>
            </x-card>
        </div>
        <aside><x-banner tone="info" title="Tus cambios están bajo tu control">Guarda cada sección por separado.</x-banner></aside>
    </div>
</x-layouts.app>
