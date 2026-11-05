@props([
    'label' => null,
    'name' => null,
    'value' => null,
    'min' => null,
    'max' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'quickToday' => false,
])

@php
    $id = $attributes->get('id') ?? 'field-'.trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name ?? 'campo'), '-');
    $message = $error ?: ($name ? $errors->first($name) : null);
    $hintId = $hint ? $id.'-hint' : null;
    $errorId = $message ? $id.'-error' : null;
    $describedBy = implode(' ', array_filter([$hintId, $errorId]));
    $current = $value ?? ($name ? old($name) : null);
@endphp

<div
    {{ $attributes->only('class')->class('space-y-1.5') }}
    @if ($quickToday)
        x-data="{
            setToday() {
                const d = new Date();
                const pad = (n) => String(n).padStart(2, '0');
                this.$refs.input.value = `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())}`;
                this.$refs.input.dispatchEvent(new Event('input', { bubbles: true }));
                this.$refs.input.focus();
            },
        }"
    @endif
>
    @if ($label)
        <label for="{{ $id }}" class="block text-[13px] font-medium text-ink">
            {{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    <div class="flex items-center gap-2">
        <div class="relative min-w-0 flex-1">
            <x-icon name="calendar" class="pointer-events-none absolute left-3 top-1/2 size-[18px] -translate-y-1/2 text-muted" />

            <input
                id="{{ $id }}"
                x-ref="input"
                name="{{ $name }}"
                type="date"
                value="{{ $current }}"
                @if ($min) min="{{ $min }}" @endif
                @if ($max) max="{{ $max }}" @endif
                @if ($required) required aria-required="true" @endif
                @if ($message) aria-invalid="true" @endif
                @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
                {{ $attributes->except('class')->class([
                    'h-11 w-full rounded-control border bg-surface pl-10 pr-3 text-sm text-ink transition-colors focus:ring-2 focus:outline-none disabled:cursor-not-allowed disabled:bg-surface-muted disabled:text-muted',
                    'border-danger focus:border-danger focus:ring-danger/20' => (bool) $message,
                    'border-border focus:border-primary focus:ring-primary/20' => ! $message,
                ]) }}
            >
        </div>

        @if ($quickToday)
            <x-button variant="secondary" x-on:click="setToday()" class="shrink-0 px-3">
                Hoy
            </x-button>
        @endif
    </div>

    @if ($hint)
        <p id="{{ $hintId }}" class="text-[12px] leading-relaxed text-muted">{{ $hint }}</p>
    @endif

    @if ($message)
        <p id="{{ $errorId }}" class="text-[12px] font-medium leading-relaxed text-danger">{{ $message }}</p>
    @endif
</div>
