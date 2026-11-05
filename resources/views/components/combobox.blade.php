@props([
    'label' => null,
    'name' => null,
    'options' => [],
    'value' => null,
    'placeholder' => 'Selecciona una opción',
    'searchPlaceholder' => 'Buscar…',
    'emptyText' => 'Sin resultados.',
    'hint' => null,
    'error' => null,
    'required' => false,
])

@php
    $id = $attributes->get('id') ?? 'field-'.trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name ?? 'campo'), '-');
    $listId = $id.'-lista';
    $message = $error ?: ($name ? $errors->first($name) : null);
    $hintId = $hint ? $id.'-hint' : null;
    $errorId = $message ? $id.'-error' : null;
    $describedBy = implode(' ', array_filter([$hintId, $errorId]));
    $current = $value ?? ($name ? old($name) : null);

    $normalized = collect($options)
        ->map(fn ($option) => [
            'value' => (string) ($option['value'] ?? ''),
            'label' => (string) ($option['label'] ?? ''),
            'meta' => $option['meta'] ?? null,
        ])
        ->values()
        ->all();

    $selectedOption = collect($normalized)->first(fn ($option) => $option['value'] === (string) $current);
@endphp

<div
    {{ $attributes->except('id')->class('space-y-1.5') }}
    x-data="combobox({ name: @js($name), options: @js($normalized), selected: @js($selectedOption) })"
    x-on:click.outside="open && close(false)"
>
    @if ($label)
        <label id="{{ $id }}-label" for="{{ $id }}" class="block text-[13px] font-medium text-ink">
            {{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    <div class="relative">
        <button
            type="button"
            id="{{ $id }}"
            x-ref="trigger"
            x-on:click="toggle()"
            x-on:keydown.arrow-down.prevent="openList()"
            x-on:keydown.arrow-up.prevent="openList()"
            aria-haspopup="listbox"
            x-bind:aria-expanded="open ? 'true' : 'false'"
            aria-controls="{{ $listId }}"
            @if ($label) aria-labelledby="{{ $id }}-label" @endif
            @if ($required) aria-required="true" @endif
            @if ($message) aria-invalid="true" @endif
            @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
            class="flex h-11 w-full items-center justify-between gap-2 rounded-control border bg-surface px-3.5 text-left text-sm transition-colors focus:ring-2 focus:outline-none"
            x-bind:class="open ? 'border-primary ring-2 ring-primary/20' : '{{ $message ? 'border-danger' : 'border-border' }}'"
        >
            <span class="min-w-0 truncate" x-bind:class="selected ? 'text-ink' : 'text-muted/80'">
                <span x-show="! selected">{{ $placeholder }}</span>
                <span x-show="selected" x-text="selected ? selected.label : ''"></span>
            </span>

            <x-icon
                name="chevron-down"
                class="size-4 shrink-0 text-muted transition-transform"
                x-bind:class="open ? 'rotate-180' : ''"
            />
        </button>

        <div
            x-show="open"
            x-cloak
            x-transition.opacity
            class="absolute z-40 mt-1 w-full overflow-hidden rounded-card border border-border bg-surface shadow-raised"
        >
            <div class="border-b border-border p-2">
                <div class="relative">
                    <x-icon name="search" class="pointer-events-none absolute left-2.5 top-1/2 size-4 -translate-y-1/2 text-muted" />

                    <input
                        type="text"
                        x-ref="search"
                        x-model="query"
                        x-on:input="highlighted = 0"
                        x-on:keydown.arrow-down.prevent="move(1)"
                        x-on:keydown.arrow-up.prevent="move(-1)"
                        x-on:keydown.enter.prevent="choose()"
                        x-on:keydown.escape.stop.prevent="close()"
                        role="combobox"
                        aria-autocomplete="list"
                        aria-expanded="true"
                        aria-controls="{{ $listId }}"
                        x-bind:aria-activedescendant="filtered.length ? '{{ $id }}-opcion-' + highlighted : null"
                        placeholder="{{ $searchPlaceholder }}"
                        aria-label="{{ $searchPlaceholder }}"
                        class="h-10 w-full rounded-control border border-border bg-surface pl-9 pr-3 text-sm text-ink placeholder:text-muted/80 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none"
                    >
                </div>
            </div>

            <ul id="{{ $listId }}" role="listbox" class="max-h-64 overflow-y-auto p-1" aria-label="{{ $label ?? 'Opciones' }}">
                <template x-for="(option, index) in filtered" :key="option.value">
                    <li
                        role="option"
                        x-bind:id="'{{ $id }}-opcion-' + index"
                        x-bind:aria-selected="selected && selected.value === option.value ? 'true' : 'false'"
                        x-on:click="select(option)"
                        x-on:mousemove="highlighted = index"
                        class="flex min-h-11 cursor-pointer items-center justify-between gap-3 rounded-control px-3 py-2 text-sm"
                        x-bind:class="index === highlighted ? 'bg-primary-soft text-primary' : 'text-ink-soft hover:bg-canvas'"
                    >
                        <span class="min-w-0">
                            <span class="block truncate" x-text="option.label"></span>
                            <span class="block truncate text-[12px] text-muted" x-show="option.meta" x-text="option.meta"></span>
                        </span>

                        <x-icon name="check" class="size-4 shrink-0" x-show="selected && selected.value === option.value" />
                    </li>
                </template>
            </ul>

            <p x-show="! filtered.length" class="px-3 py-6 text-center text-[13px] text-muted">{{ $emptyText }}</p>
        </div>
    </div>

    @if ($name)
        <input type="hidden" name="{{ $name }}" x-bind:value="selected ? selected.value : ''">
    @endif

    @if ($hint)
        <p id="{{ $hintId }}" class="text-[12px] leading-relaxed text-muted">{{ $hint }}</p>
    @endif

    @if ($message)
        <p id="{{ $errorId }}" class="text-[12px] font-medium leading-relaxed text-danger">{{ $message }}</p>
    @endif
</div>
