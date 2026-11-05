@props([
    'label' => null,
    'name' => null,
    'hint' => null,
    'error' => null,
    'required' => false,
    'rows' => 3,
    'value' => null,
])

@php
    $id = $attributes->get('id') ?? 'field-'.trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name ?? 'campo'), '-');
    $message = $error ?: ($name ? $errors->first($name) : null);
    $hintId = $hint ? $id.'-hint' : null;
    $errorId = $message ? $id.'-error' : null;
    $describedBy = implode(' ', array_filter([$hintId, $errorId]));
@endphp

<div {{ $attributes->only('class')->class('space-y-1.5') }}>
    @if ($label)
        <label for="{{ $id }}" class="block text-[13px] font-medium text-ink">
            {{ $label }}@if ($required)<span class="text-danger" aria-hidden="true"> *</span>@endif
        </label>
    @endif

    <textarea
        id="{{ $id }}"
        name="{{ $name }}"
        rows="{{ $rows }}"
        @if ($required) required aria-required="true" @endif
        @if ($message) aria-invalid="true" @endif
        @if ($describedBy) aria-describedby="{{ $describedBy }}" @endif
        {{ $attributes->except('class')->class([
            'w-full rounded-control border bg-surface px-3.5 py-2.5 text-sm leading-relaxed text-ink transition-colors placeholder:text-muted/80 focus:ring-2 focus:outline-none disabled:cursor-not-allowed disabled:bg-surface-muted disabled:text-muted',
            'border-danger focus:border-danger focus:ring-danger/20' => (bool) $message,
            'border-border focus:border-primary focus:ring-primary/20' => ! $message,
        ]) }}
    >{{ $value ?? ($name ? old($name) : null) }}</textarea>

    @if ($hint)
        <p id="{{ $hintId }}" class="text-[12px] leading-relaxed text-muted">{{ $hint }}</p>
    @endif

    @if ($message)
        <p id="{{ $errorId }}" class="text-[12px] font-medium leading-relaxed text-danger">{{ $message }}</p>
    @endif
</div>
