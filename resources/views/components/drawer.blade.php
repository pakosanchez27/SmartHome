@props([
    'name',
    'title',
    'description' => null,
    'size' => 'md',
    'side' => 'right',
])

@php
    $widths = [
        'sm' => 'sm:max-w-sm',
        'md' => 'sm:max-w-md',
        'lg' => 'sm:max-w-xl',
    ];

    $sides = [
        'right' => 'right-0 border-l',
        'left' => 'left-0 border-r',
    ];

    $enterStart = $side === 'left' ? '-translate-x-full' : 'translate-x-full';
    $leaveEnd = $side === 'left' ? '-translate-x-full' : 'translate-x-full';
@endphp

<div
    x-data="overlay('{{ $name }}')"
    x-on:open-drawer.window="if ($event.detail === '{{ $name }}') show()"
    x-on:close-drawer.window="if ($event.detail === '{{ $name }}') hide()"
    x-on:keydown="trap($event)"
    x-show="open"
    x-cloak
    class="fixed inset-0 z-50"
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
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="{{ $enterStart }}"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="{{ $leaveEnd }}"
        tabindex="-1"
        role="dialog"
        aria-modal="true"
        aria-labelledby="{{ $name }}-titulo"
        @if ($description) aria-describedby="{{ $name }}-descripcion" @endif
        @class([
            'absolute inset-y-0 flex w-full flex-col border-border bg-surface shadow-raised outline-none',
            $sides[$side] ?? $sides['right'],
            $widths[$size] ?? $widths['md'],
        ])
    >
        <div class="flex items-start justify-between gap-4 border-b border-border p-5">
            <div class="min-w-0 space-y-1">
                <h2 id="{{ $name }}-titulo" class="text-lg font-semibold leading-snug text-ink">{{ $title }}</h2>

                @if ($description)
                    <p id="{{ $name }}-descripcion" class="text-sm leading-relaxed text-muted">{{ $description }}</p>
                @endif
            </div>

            <x-icon-button label="Cerrar" icon="x" x-on:click="hide()" class="-mr-2 -mt-1" />
        </div>

        <div class="min-h-0 flex-1 space-y-4 overflow-y-auto p-5">
            {{ $slot }}
        </div>

        @isset($footer)
            <div class="flex flex-col-reverse gap-2 border-t border-border p-5 sm:flex-row sm:justify-end">
                {{ $footer }}
            </div>
        @endisset
    </div>
</div>
