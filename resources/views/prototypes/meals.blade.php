<x-layouts.app
    title="Prototipo · Planificador semanal"
    :breadcrumbs="[['label' => 'Prototipos', 'url' => route('prototypes.index')], ['label' => 'Menú semanal']]"
>
    @include('prototypes.partials.header')

    <div x-data="mealPlanner()" class="space-y-6">
        <x-page-header
            eyebrow="Alimentación · Semana de ejemplo"
            title="Planificador semanal"
            description="Organiza desayuno, comida y cena para la semana."
        />

        <div class="grid gap-3 sm:grid-cols-3">
            <x-card>
                <p class="text-[13px] text-muted">Semana de ejemplo</p>
                <p class="mt-2 text-lg font-semibold text-ink">9–15 noviembre 2026</p>
            </x-card>
            <x-card>
                <p class="text-[13px] text-muted">Comidas planeadas</p>
                <p class="mt-2 text-lg font-semibold text-ink" x-text="plannedMeals"></p>
            </x-card>
            <x-card>
                <p class="text-[13px] text-muted">Ingredientes por revisar</p>
                <p class="mt-2 text-lg font-semibold text-ink" x-text="projected.filter(item => item.missing > 0).length"></p>
            </x-card>
        </div>

        <x-banner tone="info" title="Planificar no descuenta existencias">
            Las necesidades son estimaciones ficticias. Esta demostración no modifica la despensa ni genera compras.
        </x-banner>

        <div class="grid grid-cols-7 gap-1 md:hidden" role="group" aria-label="Seleccionar día">
            <template x-for="day in days" :key="day.id">
                <button type="button" class="min-h-11 rounded-control px-1 text-xs font-semibold focus-visible:outline focus-visible:outline-2 focus-visible:outline-primary"
                    x-bind:class="selectedDay === day.id ? 'bg-primary text-white' : 'bg-surface-muted text-ink'"
                    x-bind:aria-pressed="selectedDay === day.id ? 'true' : 'false'"
                    x-bind:aria-label="day.label + ', ' + day.date"
                    x-on:click="selectedDay = day.id" x-text="day.label.slice(0, 3)"></button>
            </template>
        </div>

        <div class="grid gap-3 md:grid-cols-7">
            <template x-for="day in days" :key="day.id">
                <section x-bind:class="selectedDay === day.id ? 'block' : 'hidden md:block'" class="min-w-0 space-y-2">
                    <h2 class="rounded-control bg-surface-muted px-3 py-2 text-sm font-semibold text-ink">
                        <span x-text="day.label"></span> <span class="text-xs font-normal text-muted" x-text="day.date"></span>
                    </h2>
                    <template x-for="meal in ['breakfast', 'lunch', 'dinner']" :key="meal">
                        <x-card class="space-y-2">
                            <h3 class="text-xs font-medium uppercase text-muted" x-text="mealLabel(meal)"></h3>
                            <p class="text-sm font-semibold text-ink" x-text="recipe(day.meals[meal])?.name ?? 'Seleccionar receta'"></p>
                            <p x-show="day.meals[meal]" x-cloak class="text-xs text-muted" x-text="day.meals[meal] ? day.meals[meal].servings + ' porciones' : ''"></p>
                            <x-button variant="secondary" class="w-full" x-on:click="openPicker(day, meal)"
                                x-bind:aria-label="(day.meals[meal] ? 'Cambiar ' : 'Elegir ') + mealLabel(meal) + ' de ' + day.label">
                                <span x-text="day.meals[meal] ? 'Cambiar' : 'Elegir'"></span>
                            </x-button>
                        </x-card>
                    </template>
                </section>
            </template>
        </div>

        <x-card class="space-y-4">
            <x-section-heading title="Ingredientes estimados" description="Comparación de ejemplo con la despensa, sin descontar existencias." />
            <p x-show="projected.length === 0" class="text-sm text-muted">Agrega una receta para consultar los ingredientes.</p>
            <ul x-show="projected.length" class="divide-y divide-border">
                <template x-for="item in projected" :key="item.name + '|' + item.unit">
                    <li class="flex flex-wrap items-center justify-between gap-2 py-3 text-sm">
                        <span class="font-medium text-ink" x-text="item.name"></span>
                        <span class="text-muted" x-text="'Necesarios: ' + quantity(item.qty) + ' ' + item.unit + ' · Faltantes: ' + quantity(item.missing) + ' ' + item.unit"></span>
                    </li>
                </template>
            </ul>
        </x-card>

        <x-modal name="seleccionar-receta" title="Seleccionar receta" description="Elige una receta y sus porciones para este platillo.">
            <div class="space-y-2">
                <label for="demo-receta" class="block text-[13px] font-medium text-ink">Receta</label>
                <select id="demo-receta" x-model="picker.recipeId" class="min-h-11 w-full rounded-control border border-border bg-surface px-3 text-sm text-ink">
                    <template x-for="option in recipes" :key="option.id">
                        <option x-bind:value="option.id" x-text="option.name"></option>
                    </template>
                </select>
            </div>
            <div class="space-y-2">
                <label for="demo-porciones" class="block text-[13px] font-medium text-ink">Porciones</label>
                <input id="demo-porciones" type="number" min="1" max="24" step="1" inputmode="numeric" x-model="picker.servings"
                    class="min-h-11 w-full rounded-control border border-border bg-surface px-3 text-sm text-ink">
                <p class="text-xs text-muted">De 1 a 24 porciones. La planificación no descuenta despensa.</p>
            </div>
            <x-slot:footer>
                <x-button variant="secondary" x-on:click="hide()">Cancelar</x-button>
                <x-button x-on:click="assign()">Agregar al menú</x-button>
            </x-slot:footer>
        </x-modal>
    </div>
</x-layouts.app>
