<!DOCTYPE html>
<html lang="es" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>SmartHome — Base técnica</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-full bg-canvas font-sans text-ink antialiased">
    <main class="mx-auto flex min-h-screen max-w-3xl flex-col justify-center px-6 py-12">
        <div class="rounded-2xl border border-border bg-surface p-8 shadow-sm sm:p-10">
            <div class="flex items-center gap-3">
                <span class="grid size-11 place-items-center rounded-xl bg-primary text-white">
                    <i data-lucide="shield-check" aria-hidden="true"></i>
                </span>
                <div>
                    <h1 class="text-2xl font-semibold tracking-tight">SmartHome</h1>
                    <p class="text-sm text-muted">Sistema Inteligente de Administración del Hogar</p>
                </div>
            </div>

            <p class="mt-6 inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-sm font-medium text-success">
                <i data-lucide="check-circle-2" aria-hidden="true"></i>
                Base técnica instalada
            </p>

            <p class="mt-4 text-sm leading-6 text-muted">
                Laravel 12, MySQL, Blade, Tailwind CSS, Alpine.js, Inter y Lucide están configurados.
                Aún no hay módulos funcionales: el siguiente paso es EP-00 (dominio) y EP-01 (base).
            </p>

            <dl class="mt-8 grid grid-cols-2 gap-4 text-sm sm:grid-cols-4">
                @foreach ([
                    ['Laravel', '12'],
                    ['PHP', '8.3'],
                    ['MySQL', '8'],
                    ['Tailwind', '4'],
                ] as [$name, $version])
                    <div class="rounded-xl border border-border px-4 py-3">
                        <dt class="text-xs uppercase tracking-wide text-muted">{{ $name }}</dt>
                        <dd class="mt-1 font-semibold">{{ $version }}</dd>
                    </div>
                @endforeach
            </dl>

            <div class="mt-8 border-t border-border pt-6" x-data="{ open: false }">
                <button
                    type="button"
                    class="inline-flex items-center gap-2 text-sm font-medium text-primary hover:text-primary-hover focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2"
                    @click="open = !open"
                    :aria-expanded="open"
                    aria-controls="technical-detail"
                >
                    <i data-lucide="arrow-right" class="transition-transform" :class="open ? 'rotate-90' : ''" aria-hidden="true"></i>
                    <span x-text="open ? 'Ocultar detalle técnico' : 'Ver detalle técnico'"></span>
                </button>

                <ul id="technical-detail" x-show="open" x-cloak class="mt-4 space-y-2 text-sm text-muted">
                    <li>Zona horaria: <span class="font-medium text-ink">{{ config('app.timezone') }}</span></li>
                    <li>Moneda: <span class="font-medium text-ink">{{ config('smarthome.currency.code') }}</span></li>
                    <li>Idioma: <span class="font-medium text-ink">{{ config('app.locale') }}</span></li>
                    <li>Colas y caché: <span class="font-medium text-ink">base de datos</span></li>
                </ul>
            </div>
        </div>

        <p class="mt-6 text-center text-xs text-muted">
            SmartHome · entorno local · {{ now()->format('d/m/Y') }}
        </p>
    </main>
</body>
</html>
