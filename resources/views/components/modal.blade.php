@props([
    'name',
    'title',
    'description' => null,
    'size' => 'md',
])

@php
    $widths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-lg',
        'lg' => 'sm:max-w-2xl',
    ];
@endphp

<div
    x-data="overlay('{{ $name }}')"
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') show()"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') hide()"
    x-on:keydown="trap($event)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50 flex items-end justify-center p-0 sm:items-center sm:p-4"
>
    <div
        class="absolute inset-0 bg-ink/40"
        x-on:click="hide()"
        x-transition.opacity
        aria-hidden="true"
    ></div>

    <div
        x-ref="panel"
        x-show="open"
        x-transition
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $name }}-title"
        @if ($description) aria-describedby="{{ $name }}-description" @endif
        class="relative w-full rounded-t-sheet bg-surface shadow-raised outline-none sm:rounded-card {{ $widths[$size] ?? $widths['md'] }}"
    >
        <div class="flex items-start justify-between gap-4 border-b border-border p-5">
            <div class="min-w-0 space-y-1">
                <h2 id="{{ $name }}-title" class="text-lg font-semibold leading-snug text-ink">{{ $title }}</h2>

                @if ($description)
                    <p id="{{ $name }}-description" class="text-sm leading-relaxed text-muted">{{ $description }}</p>
                @endif
            </div>

            <x-icon-button label="Cerrar" icon="x" x-on:click="hide()" class="-mr-2 -mt-1" />
        </div>

        <div class="max-h-[70dvh] space-y-4 overflow-y-auto p-5">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex flex-col-reverse gap-2 border-t border-border p-5 sm:flex-row sm:justify-end">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
