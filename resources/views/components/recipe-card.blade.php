@props([
    'name',
    'category' => null,
    'time' => null,
    'servings' => null,
    'description' => null,
    'image' => null,
    'tags' => [],
    'favorite' => false,
    'href' => null,
])

<article {{ $attributes->class('flex h-full flex-col overflow-hidden rounded-card border border-border bg-surface shadow-card') }}>
    <div class="relative flex aspect-[4/3] items-center justify-center bg-primary-soft">
        @if ($image)
            <img src="{{ $image }}" alt="" class="absolute inset-0 size-full object-cover" loading="lazy">
        @else
            <x-icon name="chef-hat" class="size-10 text-primary/40" />
        @endif

        <div class="absolute inset-x-3 top-3 flex items-start justify-between gap-2">
            @if ($category)
                <x-badge tone="primary" class="bg-surface/95 backdrop-blur">{{ $category }}</x-badge>
            @endif

            @if ($favorite)
                <x-badge tone="warning" icon="star" class="bg-surface/95 backdrop-blur">Favorita</x-badge>
            @endif
        </div>
    </div>

    <div class="flex min-h-0 flex-1 flex-col gap-3 p-4">
        <div class="space-y-1">
            <h3 class="text-[15px] font-semibold leading-snug text-ink">
                @if ($href)
                    <a href="{{ $href }}" class="rounded-control transition-colors hover:text-primary">{{ $name }}</a>
                @else
                    {{ $name }}
                @endif
            </h3>

            @if ($description)
                <p class="line-clamp-2 text-[12px] leading-relaxed text-muted">{{ $description }}</p>
            @endif
        </div>

        @if ($time || $servings)
            <div class="flex flex-wrap items-center gap-x-3 gap-y-1 text-[12px] text-muted">
                @if ($time)
                    <span class="inline-flex items-center gap-1">
                        <x-icon name="clock" class="size-3.5" />
                        {{ $time }} min
                    </span>
                @endif

                @if ($servings)
                    <span class="inline-flex items-center gap-1">
                        <x-icon name="users" class="size-3.5" />
                        {{ $servings }} porciones
                    </span>
                @endif
            </div>
        @endif

        @if (count($tags) > 0)
            <div class="flex flex-wrap gap-1.5">
                @foreach ($tags as $tag)
                    <x-badge tone="outline">{{ $tag }}</x-badge>
                @endforeach
            </div>
        @endif

        @isset($actions)
            <div class="mt-auto flex flex-wrap items-center gap-2 border-t border-border pt-3">{{ $actions }}</div>
        @endisset
    </div>
</article>
