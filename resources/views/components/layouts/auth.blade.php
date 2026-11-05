@props(['title'])
<!DOCTYPE html>
<html lang="es-MX">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title }} · SmartHome</title>
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
        <div class="w-full sm:rounded-2xl sm:border sm:border-border sm:bg-surface sm:p-8">{{ $slot }}</div>
        <p class="text-xs text-muted">SmartHome</p>
    </main>
</body>
</html>
