<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Iniciar sesión · SmartHome</title>
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
            <form method="POST" action="{{ route('login.store') }}" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <h1 class="text-[28px] font-semibold leading-tight">Qué gusto verte</h1>
                    <p class="text-sm text-muted">Entra para organizar lo que comparten en casa.</p>
                </div>
                @if (session('status'))
                    <x-banner tone="success">{{ session('status') }}</x-banner>
                @endif
                <x-input label="Correo electrónico" name="email" type="email" required autocomplete="username" maxlength="255" />
                <div x-data="{ visible: false }" class="relative">
                    <x-input label="Contraseña" name="password" type="password" required autocomplete="current-password" x-bind:type="visible ? 'text' : 'password'" class="pr-16" />
                    <button type="button" x-on:click="visible = !visible" x-bind:aria-pressed="visible ? 'true' : 'false'" class="absolute right-2 top-6 min-h-11 px-2 text-xs font-semibold text-primary" x-text="visible ? 'Ocultar' : 'Mostrar'">Mostrar</button>
                </div>
                <label class="flex min-h-11 items-center gap-3 text-sm">
                    <input type="checkbox" name="remember" value="1" class="size-5 accent-primary">
                    Recordarme en este dispositivo
                </label>
                <x-button type="submit" class="w-full">Iniciar sesión</x-button>
                <a href="{{ route('password.request') }}" class="inline-flex min-h-11 items-center text-sm font-semibold text-primary">Olvidé mi contraseña</a>
                <p class="text-xs text-muted">Si usas un dispositivo compartido, no actives Recordarme.</p>
            </form>
        </div>
        <p class="text-xs text-muted">SmartHome</p>
    </main>
</body>
</html>
