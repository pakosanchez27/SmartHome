@props([
    'label',
    'name',
    'value' => '1',
    'checked' => false,
    'description' => null,
])

@php
    $id = $attributes->get('id') ?? 'check-'.trim(preg_replace('/[^A-Za-z0-9]+/', '-', $name ?? 'campo'), '-');
    $isChecked = old($name) !== null ? (bool) old($name) : (bool) $checked;
@endphp

<label for="{{ $id }}" class="flex min-h-11 cursor-pointer items-start gap-3 py-1">
    <input
        id="{{ $id }}"
        name="{{ $name }}"
        type="checkbox"
        value="{{ $value }}"
        @checked($isChecked)
        {{ $attributes->class(['mt-0.5 size-5 shrink-0 cursor-pointer rounded border-border accent-primary focus:ring-2 focus:ring-primary/20']) }}
    >

    <span class="min-w-0 space-y-0.5">
        <span class="block text-sm font-medium leading-snug text-ink">{{ $label }}</span>

        @if ($description)
            <span class="block text-[12px] leading-relaxed text-muted">{{ $description }}</span>
        @endif
    </span>
</label>
