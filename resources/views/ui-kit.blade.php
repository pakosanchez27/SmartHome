@php
    $mxn = fn (float $amount): string => '$'.number_format($amount, 2, '.', ',');
@endphp

<x-layouts.app
    title="Sistema de diseño"
    :breadcrumbs="[['label' => 'Sistema de diseño']]"
>
    <x-banner tone="info" title="EP-02 · Biblioteca de componentes">
        Esta pantalla es el catálogo vivo del sistema de diseño: tokens, controles y estados.
        El tablero B01 y el resto de módulos se construyen sobre estos componentes.
    </x-banner>

    <x-page-header
        eyebrow="Diseño · EP-02"
        title="Sistema de diseño"
        description="Componentes Blade reutilizables que reproducen el archivo de Figma. Cualquier vista nueva debe componerse con estas piezas."
    >
        <x-slot:actions>
            <x-button
                href="https://www.figma.com/design/LtvQfQCRtzxeS5iTQj2eqe"
                target="_blank"
                rel="noopener noreferrer"
                variant="secondary"
            >
                <x-icon name="book-open" class="size-[18px]" />
                Abrir Figma
            </x-button>
        </x-slot:actions>
    </x-page-header>

    <x-tabs
        label="Secciones de la biblioteca"
        :items="[
            ['label' => 'Paleta', 'href' => '#paleta'],
            ['label' => 'Tipografía', 'href' => '#tipografia'],
            ['label' => 'Controles', 'href' => '#controles'],
            ['label' => 'Datos', 'href' => '#datos'],
            ['label' => 'Estados', 'href' => '#estados'],
        ]"
    />

    {{-- Paleta --}}
    <section id="paleta" aria-labelledby="paleta-titulo" class="space-y-3">
        <x-section-heading
            title="Color funcional"
            description="Un solo lugar para los tokens. No se usan colores fuera de resources/css/app.css."
        />

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 xl:grid-cols-7">
            @foreach ([
                ['Acción', 'bg-primary', 'text-white', '#0F766E'],
                ['Texto', 'bg-ink', 'text-white', '#0F172A'],
                ['Secundario', 'bg-muted', 'text-white', '#64748B'],
                ['IA', 'bg-ai', 'text-white', '#6366F1'],
                ['Éxito', 'bg-success', 'text-white', '#15803D'],
                ['Aviso', 'bg-warning', 'text-white', '#B45309'],
                ['Peligro', 'bg-danger', 'text-white', '#B91C1C'],
            ] as [$label, $swatch, $text, $hex])
                <x-card padding="p-3" class="space-y-2">
                    <div class="{{ $swatch }} flex h-12 items-end justify-end rounded-[8px] p-2">
                        <span class="text-[10px] font-medium {{ $text }}">{{ $hex }}</span>
                    </div>
                    <p class="text-[12px] font-medium text-ink">{{ $label }}</p>
                </x-card>
            @endforeach
        </div>

        <div class="grid gap-3 sm:grid-cols-3">
            <x-card class="flex items-center justify-between gap-3">
                <span class="text-[13px] font-medium text-ink">Fondo</span>
                <span class="rounded-control border border-border bg-canvas px-2 py-1 text-[11px] text-muted">#F8FAFC</span>
            </x-card>
            <x-card class="flex items-center justify-between gap-3">
                <span class="text-[13px] font-medium text-ink">Superficie</span>
                <span class="rounded-control border border-border bg-surface px-2 py-1 text-[11px] text-muted">#FFFFFF</span>
            </x-card>
            <x-card class="flex items-center justify-between gap-3">
                <span class="text-[13px] font-medium text-ink">Borde</span>
                <span class="rounded-control border border-border bg-surface px-2 py-1 text-[11px] text-muted">#E2E8F0</span>
            </x-card>
        </div>
    </section>

    {{-- Tipografía --}}
    <section id="tipografia" aria-labelledby="tipografia-titulo" class="space-y-3">
        <x-section-heading
            title="Inter · jerarquía y cifras"
            description="Cifras siempre con tabular-nums para que no bailen entre estados."
        />

        <x-card class="space-y-4">
            <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border pb-3">
                <span class="text-[21px] font-semibold tracking-tight text-ink">SmartHome</span>
                <span class="text-[11px] text-muted">Marca · 21 / semibold</span>
            </div>
            <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border pb-3">
                <span class="text-[28px] font-semibold leading-tight tracking-tight text-ink">Un hogar en equilibrio</span>
                <span class="text-[11px] text-muted">Título · 28 / semibold</span>
            </div>
            <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border pb-3">
                <span class="text-[17px] font-semibold text-ink">Título de sección</span>
                <span class="text-[11px] text-muted">Sección · 17 / semibold</span>
            </div>
            <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-border pb-3">
                <span class="text-sm text-ink-soft">Cuerpo 14 · $15,000.00 MXN</span>
                <span class="text-[11px] text-muted">Cuerpo · 14 / regular</span>
            </div>
            <div class="flex flex-wrap items-baseline justify-between gap-2">
                <span class="text-[11px] uppercase tracking-[0.08em] text-muted">Etiquetas 11 · radios 8 / 12 / 16</span>
                <span class="text-[11px] text-muted">Etiqueta · 11</span>
            </div>
        </x-card>
    </section>

    {{-- Controles --}}
    <section id="controles" aria-labelledby="controles-titulo" class="space-y-3">
        <x-section-heading
            title="Botones · 44 px mínimo"
            description="Primario, secundario y destructivo con estados default, loading y disabled."
        />

        <x-card class="space-y-5">
            <div class="flex flex-wrap items-center gap-3">
                <x-button x-on:click="$store.toasts.push('Cambios guardados.', 'success')">Guardar</x-button>
                <x-button variant="secondary" x-on:click="$store.toasts.push('Vista previa lista.', 'info')">Vista previa</x-button>
                <x-button variant="destructive" x-on:click="$dispatch('open-modal', 'demo-confirmar')">Eliminar</x-button>
                <x-button variant="ghost">Cancelar</x-button>
                <x-button variant="secondary" loading>Guardando</x-button>
                <x-button disabled>Guardar</x-button>
            </div>

            <div class="flex flex-wrap items-center gap-2 border-t border-border pt-4">
                <x-icon-button label="Buscar" icon="search" />
                <x-icon-button label="Filtrar" icon="funnel" />
                <x-icon-button label="Eliminar" icon="trash" />
                <span class="text-[12px] text-muted">Icon buttons de 44 px con <code class="rounded bg-surface-muted px-1 py-0.5">aria-label</code> obligatorio.</span>
            </div>
        </x-card>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-card class="space-y-4">
                <x-section-heading title="Input" description="Label persistente, ayuda y error inline." />

                <x-input label="Concepto" name="demo_concepto" placeholder="Supermercado" hint="Máximo 120 caracteres." />

                <x-input label="Importe" name="demo_importe_ok" money placeholder="0.00" leading="wallet" hint="Se guarda como DECIMAL, nunca con decimales flotantes." />

                <x-input
                    label="Importe con error"
                    name="demo_importe"
                    money
                    value="-20.00"
                    error="Ingresa un importe mayor que cero."
                />

                <x-select label="Cuenta de origen" name="demo_cuenta" placeholder="Selecciona una cuenta">
                    <option value="efectivo">Efectivo</option>
                    <option value="debito">Débito</option>
                </x-select>
            </x-card>

            <x-card class="space-y-4">
                <x-section-heading title="Búsqueda y segmentado" description="Filtros con scroll horizontal en móvil, sin tablas." />

                <x-input name="demo_buscar" type="search" placeholder="Buscar receta" leading="search" aria-label="Buscar receta" />

                <x-tabs
                    label="Categorías de receta"
                    :items="[
                        ['label' => 'Todas', 'href' => '#controles', 'active' => true],
                        ['label' => 'Favoritas', 'href' => '#controles'],
                    ]"
                />

                <div class="flex flex-wrap gap-2">
                    <x-badge tone="outline" icon="users">Familiar</x-badge>
                    <x-badge tone="outline" icon="user">Personal</x-badge>
                    <x-badge tone="success">Activa</x-badge>
                    <x-badge tone="warning">Por actualizar</x-badge>
                    <x-badge tone="ai">IA</x-badge>
                    <x-badge tone="danger">Vencida</x-badge>
                </div>

                <x-quantity-stepper label="Cantidad · unidad" name="demo_porciones" :value="2" unit="porciones" :min="1" :max="12" />

                <x-checkbox name="demo_comprado" label="Ya lo compré" description="Marcar no registra todavía el egreso." />
            </x-card>
        </div>
    </section>

    {{-- Datos --}}
    <section id="datos" aria-labelledby="datos-titulo" class="space-y-3">
        <x-section-heading
            title="Datos y tarjetas"
            description="KPI, barras de presupuesto y listas. En móvil, listas; nunca tablas horizontales."
        />

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <x-kpi-card label="Disponible actual" :value="$mxn(15000)" hint="Lo que puedes usar hoy, después de reservar." tone="primary" />
            <x-kpi-card label="Ingresos recibidos" :value="$mxn(24000)" hint="Recibido, no solo esperado." />
            <x-kpi-card label="Gastos registrados" :value="$mxn(7200)" hint="De $16,000.00 presupuestados." />
            <x-kpi-card label="Presupuesto restante" :value="$mxn(8800)" hint="55% aún disponible en el plan." />
        </div>

        <div class="grid gap-4 lg:grid-cols-2">
            <x-card class="space-y-4">
                <x-section-heading title="Barra de presupuesto" />

                <x-progress
                    label="Real / planeado"
                    :value="7200"
                    :max="16000"
                    value-label="$7,200.00 / $16,000.00"
                />

                <p class="text-[12px] text-muted">45% del presupuesto utilizado</p>

                <div class="border-t border-border pt-3">
                    <x-list-row title="Renta" subtitle="Movimiento · 1 nov · Cuenta familiar" meta="Registrado">
                        <x-slot:trailing>
                            <span class="text-sm font-semibold tabular-nums text-ink">−$6,000.00</span>
                        </x-slot:trailing>
                    </x-list-row>

                    <x-list-row title="Supermercado" subtitle="Compra semanal · 3 pendientes" meta="Por confirmar">
                        <x-slot:trailing>
                            <span class="text-sm font-semibold tabular-nums text-muted">$360.00</span>
                        </x-slot:trailing>
                    </x-list-row>
                </div>
            </x-card>

            <x-card class="space-y-4">
                <x-section-heading title="Tabla (solo desktop)" description="En móvil se reemplaza por la lista de arriba." />

                <div class="hidden overflow-x-auto md:block">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-border text-[11px] uppercase tracking-wide text-muted">
                            <tr>
                                <th scope="col" class="py-2 pr-3 font-semibold">Concepto</th>
                                <th scope="col" class="py-2 pr-3 font-semibold">Estado</th>
                                <th scope="col" class="py-2 text-right font-semibold">Monto</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            <tr>
                                <td class="py-3 pr-3 text-ink">Renta</td>
                                <td class="py-3 pr-3"><x-badge tone="success">Pagado</x-badge></td>
                                <td class="py-3 text-right font-semibold tabular-nums text-ink">−$6,000.00</td>
                            </tr>
                            <tr>
                                <td class="py-3 pr-3 text-ink">Servicios del hogar</td>
                                <td class="py-3 pr-3"><x-badge tone="warning">Pendiente</x-badge></td>
                                <td class="py-3 text-right font-semibold tabular-nums text-ink">−$1,500.00</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="space-y-2 md:hidden">
                    <x-badge tone="info">Vista móvil en lista</x-badge>
                    <p class="text-[12px] leading-relaxed text-muted">La tabla se oculta porque exige scroll horizontal; la misma información se muestra como lista en móvil.</p>
                </div>
            </x-card>
        </div>
    </section>

    {{-- Estados --}}
    <section id="estados" aria-labelledby="estados-titulo" class="space-y-3">
        <x-section-heading
            title="Feedback y estados"
            description="Carga, vacío, error recuperable y confirmación. Toda pantalla debe cubrirlos."
        />

        <div class="grid gap-4 lg:grid-cols-3">
            <x-card class="space-y-3">
                <p class="text-[13px] font-semibold text-ink">Cargando</p>

                <div class="space-y-2" aria-hidden="true">
                    <x-skeleton class="h-4 w-40" />
                    <x-skeleton class="h-11 w-full" />
                    <x-skeleton class="h-11 w-full" />
                    <x-skeleton class="h-11 w-2/3" />
                </div>

                <p class="sr-only" role="status">Cargando información del hogar…</p>
            </x-card>

            <x-card>
                <x-empty-state
                    icon="book-open"
                    title="Aún no hay recetas"
                    description="Agrega la primera receta de la familia para empezar a planear la semana."
                >
                    <x-slot:actions>
                        <x-button x-on:click="$store.toasts.push('Aquí se abrirá el editor de recetas (EP-09).', 'info')">
                            <x-icon name="plus" class="size-[18px]" />
                            Nueva receta
                        </x-button>
                    </x-slot:actions>
                </x-empty-state>
            </x-card>

            <x-card class="space-y-3">
                <p class="text-[13px] font-semibold text-ink">Error recuperable</p>

                <x-banner tone="danger" title="No pudimos cargar tus cuentas">
                    Revisa tu conexión e inténtalo de nuevo. No se guardó ningún cambio.
                    <x-slot:actions>
                        <x-button variant="secondary" x-on:click="$store.toasts.push('Reintentando…', 'info')">
                            <x-icon name="refresh-cw" class="size-[18px]" />
                            Reintentar
                        </x-button>
                    </x-slot:actions>
                </x-banner>

                <x-banner tone="success" title="Compra confirmada">
                    Se registró un solo egreso y las entradas de inventario correspondientes.
                </x-banner>
            </x-card>
        </div>

        <x-card class="flex flex-wrap items-center justify-between gap-3">
            <div class="space-y-1">
                <p class="text-[13px] font-semibold text-ink">Toast y modal de decisión</p>
                <p class="text-[12px] text-muted">El toast confirma algo no crítico; el modal se reserva para decisiones importantes.</p>
            </div>

            <div class="flex flex-wrap gap-2">
                <x-button variant="secondary" x-on:click="$store.toasts.push('Receta guardada.', 'success')">Probar toast</x-button>
                <x-button x-on:click="$dispatch('open-modal', 'demo-confirmar')">Confirmar compra</x-button>
            </div>
        </x-card>
    </section>

    <p class="pb-4 text-[11px] text-muted">
        Datos demostrativos en MXN · No se ha registrado ninguna operación real.
    </p>

    <x-modal
        name="demo-confirmar"
        title="¿Confirmar compra?"
        description="Revisa el total antes de registrar el gasto."
    >
        <div class="flex items-center justify-between border-b border-border pb-3 text-sm">
            <span class="text-muted">Total</span>
            <span class="font-semibold tabular-nums text-ink">$198.00</span>
        </div>

        <x-banner tone="warning" title="Esto crea un egreso">
            Se registrará un movimiento en la cuenta elegida y las entradas de inventario. Los artículos no comprados quedan pendientes.
        </x-banner>

        <x-slot:footer>
            <x-button variant="secondary" x-on:click="open = false">Volver a revisar</x-button>
            <x-button x-on:click="open = false; $store.toasts.push('Compra confirmada: un egreso, un inventario.', 'success')">
                Confirmar compra
            </x-button>
        </x-slot:footer>
    </x-modal>
</x-layouts.app>
