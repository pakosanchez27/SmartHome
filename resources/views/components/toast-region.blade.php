@props([])

<div
    class="pointer-events-none fixed inset-x-4 bottom-24 z-50 flex flex-col items-stretch gap-2 sm:inset-x-auto sm:bottom-6 sm:right-6 sm:items-end lg:bottom-6"
    aria-live="polite"
    aria-atomic="false"
>
    <template x-for="toast in $store.toasts.items" :key="toast.id">
        <div
            x-transition
            class="pointer-events-auto flex w-full items-start gap-3 rounded-card border border-border bg-surface p-4 shadow-raised sm:w-80"
        >
            <span
                class="mt-0.5 size-2.5 shrink-0 rounded-full"
                x-bind:class="{
                    'bg-success': toast.tone === 'success',
                    'bg-danger': toast.tone === 'error',
                    'bg-info': toast.tone === 'info',
                }"
                aria-hidden="true"
            ></span>

            <p class="min-w-0 flex-1 text-[13px] leading-relaxed text-ink" x-text="toast.message"></p>

            <button
                type="button"
                x-on:click="$store.toasts.dismiss(toast.id)"
                aria-label="Cerrar aviso"
                class="-m-2 inline-flex size-9 items-center justify-center rounded-control text-muted hover:bg-canvas hover:text-ink"
            >
                <x-icon name="x" class="size-4" />
            </button>
        </div>
    </template>
</div>
