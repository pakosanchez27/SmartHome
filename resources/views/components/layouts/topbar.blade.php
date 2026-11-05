@props([
    'breadcrumbs' => [],
])

<header class="sticky top-0 z-30 border-b border-border bg-surface/95 backdrop-blur">
    <div class="flex h-16 items-center gap-3 px-4 sm:px-6 lg:px-8">
        <div class="min-w-0 flex-1">
            <nav aria-label="Ruta de navegación" class="hidden min-w-0 items-center gap-1.5 text-sm text-muted lg:flex">
                @foreach ($breadcrumbs as $crumb)
                    @if (! $loop->last && ! empty($crumb['url']))
                        <a href="{{ $crumb['url'] }}" class="truncate rounded-control px-1 py-0.5 transition-colors hover:text-ink">{{ $crumb['label'] }}</a>
                        <span aria-hidden="true" class="text-border-strong">/</span>
                    @else
                        <span @if ($loop->last) aria-current="page" @endif @class(['truncate', 'font-medium text-ink' => $loop->last])>{{ $crumb['label'] }}</span>
                        @if (! $loop->last)
                            <span aria-hidden="true" class="text-border-strong">/</span>
                        @endif
                    @endif
                @endforeach
            </nav>

            <div class="flex min-w-0 items-center gap-2 lg:hidden">
                <span class="flex size-8 shrink-0 items-center justify-center rounded-control bg-primary-soft text-primary">
                    <x-icon name="house" class="size-[18px]" />
                </span>

                <span class="min-w-0 truncate text-sm font-semibold text-ink">Hogar Rivera</span>

                <span class="rounded-full bg-surface-muted px-2 py-0.5 text-[10px] font-medium text-muted">2 integrantes</span>
            </div>
        </div>

        <div class="flex shrink-0 items-center gap-1 sm:gap-2">
            <button
                type="button"
                disabled
                title="Notificaciones · disponible en EP-18"
                aria-label="Notificaciones (disponible en EP-18)"
                class="inline-flex size-11 cursor-not-allowed items-center justify-center rounded-control text-muted opacity-60"
            >
                <x-icon name="bell" class="size-5" />
            </button>

            <span class="flex size-9 items-center justify-center rounded-control bg-primary-soft text-[12px] font-semibold text-primary" aria-hidden="true">
                AR
            </span>
        </div>
    </div>
</header>
