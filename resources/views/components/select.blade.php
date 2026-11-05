@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'placeholder' => null,
    'value' => null,
])

@php
    $id = $attributes->get('id') ?? 'field-'.trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name ?? 'campo'), '-');
    $message = $error ?: ($name ? $errors->first($name) : null);
    $hintId = $hint ? $id.'-hint' : null;
    $errorId = $message ? $id.'-error' : null;
    $describedBy = implode(' ', array_filter([$hintId, $errorId]));
    $selected = $value ?? ($name ? old($name) : null);
@endphp

<div {{ $attributes->only('class')->class('space-y-1.5') }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-[13px] font-medium text-ink">
            {{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    <select
        id="{{ $id }}"
        name="{{ $name }}"
        @if ($required) required aria-required="true" @endif
        @if ($message) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('class')->class([
            'h-11 w-full rounded-control border bg-surface px-3 text-sm text-ink transition-colors focus:ring-2 focus:outline-none disabled:cursor-not-allowed disabled:bg-surface-muted disabled:text-muted',
            'border-danger focus:border-danger focus:ring-danger/20' => (bool) $message,
            'border-border focus:border-primary focus:ring-primary/20' => ! $message,
        ]) }}
    >
        @if ($placeholder)
            <option value="" @selected($selected === null || $selected === '')>{{ $placeholder }}</option>
        @endif

        {{ $slot }}
    </select>

    @if ($hint)
        <p id="{{ $hintId }}" class="text-[12px] leading-relaxed text-muted">{{ $hint }}</p>
    @endif

    @if ($message)
        <p id="{{ $errorId }}" class="text-[12px] font-medium leading-relaxed text-danger">{{ $message }}</p>
    @endif
</div>
