@props([
    'title' => null,
    'breadcrumbs' => [],
    'demo' => true,
])

<!DOCTYPE html>
<html lang="es-MX" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#0f766e">

    <title>{{ $title ? $title.' · ' : '' }}SmartHome</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full">
    <a
        href="#contenido"
        class="sr-only z-50 rounded-control bg-primary px-4 py-2.5 text-sm font-semibold text-white focus:not-sr-only focus:fixed focus:left-4 focus:top-4"
    >
        Saltar al contenido
    </a>

    <div x-data="shell()" class="min-h-dvh lg:flex">
        <x-layouts.sidebar :demo="$demo" />

        <div class="flex min-w-0 flex-1 flex-col">
            <x-layouts.topbar :breadcrumbs="$breadcrumbs" :demo="$demo" />

            <main id="contenido" tabindex="-1" class="flex-1 px-4 pb-28 pt-5 outline-none sm:px-6 lg:px-8 lg:pb-10 lg:pt-6">
                <div class="mx-auto w-full max-w-[1200px] space-y-6">
                    {{ $slot }}
                </div>
            </main>
        </div>

        <x-layouts.bottom-nav />
        <x-layouts.more-sheet />
        <x-toast-region />
    </div>
</body>
</html>
