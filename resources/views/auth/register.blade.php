<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Crear mi hogar · SmartHome</title>
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
            <form method="POST" action="{{ route('household.register.store') }}" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <h1 class="text-[28px] font-semibold leading-tight">Crea tu hogar</h1>
                    <p class="text-sm text-muted">Prepara la cuenta que administrará la casa. Después podrás invitar a tu pareja.</p>
                </div>

                <div>
                    <h2 class="text-[13px] font-semibold uppercase tracking-wide text-muted">Tu cuenta</h2>
                    <p class="mt-1 text-xs text-muted">Serás la primera persona administradora del hogar.</p>
                </div>
                <x-input label="Nombre" name="name" type="text" required autocomplete="name" maxlength="255" />
                <x-input label="Correo electrónico" name="email" type="email" required autocomplete="username" maxlength="255" />
                <div x-data="{ visible: false }" class="relative">
                    <x-input label="Contraseña" name="password" type="password" required autocomplete="new-password" x-bind:type="visible ? 'text' : 'password'" class="pr-16" hint="Mínimo 8 caracteres." />
                    <button type="button" x-on:click="visible = !visible" x-bind:aria-pressed="visible ? 'true' : 'false'" class="absolute right-2 top-6 min-h-11 px-2 text-xs font-semibold text-primary" x-text="visible ? 'Ocultar' : 'Mostrar'">Mostrar</button>
                </div>
                <x-input label="Repite la contraseña" name="password_confirmation" type="password" required autocomplete="new-password" />

                <div>
                    <h2 class="text-[13px] font-semibold uppercase tracking-wide text-muted">Tu hogar</h2>
                    <p class="mt-1 text-xs text-muted">Estos datos luego pueden ajustarse en Configuración.</p>
                </div>
                <x-input label="Nombre del hogar" name="household_name" type="text" required maxlength="120" placeholder="P. ej. Casa de la familia Rosales" />
                <x-select label="Zona horaria" name="timezone" required hint="Se usa para fechas y quincenas.">
                    <option value="America/Mexico_City" @selected(old('timezone', 'America/Mexico_City') === 'America/Mexico_City')">Centro · América/Mexico_City</option>
                    <option value="America/Merida" @selected(old('timezone') === 'America/Merida')">Península · América/Merida</option>
                    <option value="America/Monterrey" @selected(old('timezone') === 'America/Monterrey')">Noreste · América/Monterrey</option>
                    <option value="America/Mazatlan" @selected(old('timezone') === 'America/Mazatlan')">Pacífico · América/Mazatlan</option>
                    <option value="America/Hermosillo" @selected(old('timezone') === 'America/Hermosillo')">Sonora · América/Hermosillo</option>
                    <option value="America/Tijuana" @selected(old('timezone') === 'America/Tijuana')">Frontera norte · América/Tijuana</option>
                </x-select>
                <div class="rounded-control border border-border bg-surface-muted px-3 py-2.5 text-[12px] leading-relaxed text-muted">
                    Moneda <strong class="font-semibold text-ink">MXN</strong> y quincenas con corte el <strong class="font-semibold text-ink">día 15</strong> por defecto; el remanente de cada quincena se traslada al siguiente periodo.
                </div>

                <x-button type="submit" class="w-full">Crear mi hogar</x-button>
                <p class="text-center text-sm text-muted">
                    ¿Ya tienes cuenta?
                    <a href="{{ route('login') }}" class="font-semibold text-primary">Inicia sesión</a>
                </p>
            </form>
        </div>
        <p class="text-xs text-muted">SmartHome</p>
    </main>
</body>
</html>
