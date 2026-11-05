# Guía UI · Sistema de diseño SmartHome (EP-02)

> Fuente de verdad visual: [Figma · SmartHome](https://www.figma.com/design/LtvQfQCRtzxeS5iTQj2eqe/Sin-t%C3%ADtulo)
> Secciones: `03 — Desktop Screens` (1440), `04 — Mobile Screens` (390), `06 — Dialogs & States`, `02 — Components`.
> Regla permanente en `AGENTS.md` §10.1: **toda vista nueva se implementa desde su frame de Figma**.

## 1. Tokens

Definidos en `resources/css/app.css` con `@theme` de Tailwind 4. No usar colores fuera de esta lista.

| Token | Valor | Uso |
|---|---|---|
| `primary` | `#0F766E` | Acción principal, selección |
| `primary-hover` | `#115E59` | Hover de acción |
| `primary-soft` | `#F0FDFA` | Fondo de ítem activo, KPI destacado |
| `canvas` | `#F8FAFC` | Fondo de la aplicación |
| `surface` | `#FFFFFF` | Tarjetas, sidebar, topbar |
| `surface-muted` | `#F1F5F9` | Zonas neutras, skeletons |
| `ink` | `#0F172A` | Texto principal |
| `ink-soft` | `#334155` | Texto secundario fuerte |
| `muted` | `#64748B` | Texto secundario, iconos inactivos |
| `border` | `#E2E8F0` | Bordes y divisores |
| `border-strong` | `#CBD5E1` | Separadores de breadcrumb |
| `ai` / `ai-soft` | `#6366F1` / `#EEF2FF` | Acento IA y foco visible |
| `success` / `-soft` | `#15803D` / `#F0FDF4` | Confirmado, pagado, en stock |
| `warning` / `-soft` | `#B45309` / `#FFFBEB` | Pendiente, stock bajo |
| `danger` / `-soft` | `#B91C1C` / `#FEF2F2` | Error, agotado, destructivo |
| `info` / `-soft` | `#0369A1` / `#F0F9FF` | Información neutral |

Radios: `rounded-control` (8 px), `rounded-card` (12 px), `rounded-sheet` (16 px), `rounded-full` para pills.
Sombras: `shadow-card` (reposo), `shadow-raised` (modales, sheets, toasts).

## 2. Tipografía

Inter Variable. Escala usada en los mockups:

| Rol | Tamaño / peso |
|---|---|
| Marca (`SmartHome`) | 21 px semibold |
| Título de página | 28 px desktop / 24 px móvil semibold |
| Título de sección en tarjeta | 17 px semibold |
| Título de modal | 18 px semibold |
| Cuerpo | 14 px regular |
| Navegación lateral | 13 px medium |
| Texto auxiliar, hints | 12–13 px |
| Etiquetas y metadatos | 11 px uppercase con tracking |
| Barra inferior móvil | 10 px |

Las cifras monetarias usan `tabular-nums` para no bailar entre estados.

## 3. Breakpoints y layout

| Ancho | Comportamiento |
|---|---|
| 360 px | Mínimo soportado; sin scroll horizontal de página |
| 390 px | Referencia móvil de Figma; barra inferior + hoja “Más” |
| 768 px | Tablet; grids de 2 columnas, sidebar aún oculta |
| 1024 px (`lg`) | Aparece sidebar de 224 px (colapsable a 76 px) |
| 1280 / 1440 px | Contenido centrado con `max-w-[1200px]` |

- Sidebar: `x-data="shell()"`, estado persistido en `localStorage` (`smarthome:sidebar`).
- Móvil: barra inferior fija **Inicio / Finanzas / Compras / Alimentación / Más** y hoja “Más”.
- Los módulos sin ruta implementada se renderizan con `aria-disabled="true"` y `title="… disponible en EP-XX"`. **Nunca** enlaces `href="#"` ni botones que aparenten funcionar.
- Contenido móvil lleva `pb-28` para no quedar bajo la barra inferior.

## 4. Componentes Blade

Todos viven en `resources/views/components/`.

### Acción

```blade
<x-button>Guardar</x-button>
<x-button variant="secondary" href="{{ route('home') }}">Ver</x-button>
<x-button variant="destructive" x-on:click="$dispatch('open-modal', 'confirmar')">Eliminar</x-button>
<x-button loading>Guardando</x-button>
<x-icon-button label="Buscar" icon="search" />
```

Variantes: `primary`, `secondary`, `destructive`, `ghost`, `ai`. Altura mínima 44 px. `loading` deshabilita y marca `aria-busy`.

### Formularios

```blade
<x-input label="Concepto" name="concepto" required hint="Máximo 120 caracteres." />
<x-input label="Importe" name="monto" money leading="wallet" />
<x-select label="Cuenta" name="cuenta" placeholder="Selecciona una cuenta">…</x-select>
<x-textarea label="Notas" name="notas" />
<x-checkbox name="comprado" label="Ya lo compré" description="Aún no registra el egreso." />
<x-quantity-stepper label="Porciones" name="porciones" :value="2" unit="porciones" />
```

- Label persistente unido por `for`/`id`; el id se deriva del `name`.
- Errores: prop `error` o `$errors->first($name)`; se renderizan inline con `aria-invalid` y `aria-describedby`.
- Importes: `money` usa `inputmode="decimal"`; el backend sigue siendo responsable de validar y guardar `DECIMAL(14,2)`.

### Contenedores y datos

```blade
<x-card>…</x-card>
<x-section-heading title="…" description="…">
    <x-slot:actions>…</x-slot:actions>
</x-section-heading>
<x-kpi-card label="Disponible actual" value="$15,000.00" tone="primary" />
<x-progress label="Real / planeado" :value="7200" :max="16000" value-label="$7,200 / $16,000" />
<x-list-row title="Renta" subtitle="1 nov · Cuenta familiar" meta="Registrado">…</x-list-row>
<x-badge tone="success">Pagado</x-badge>
<x-tabs :items="[['label' => 'Todas', 'href' => '…', 'active' => true]]" />
```

### Feedback

```blade
<x-banner tone="warning" title="Esto crea un egreso">…</x-banner>
<x-empty-state icon="book-open" title="Aún no hay recetas" description="…">…</x-empty-state>
<x-skeleton class="h-4 w-40" />
<x-modal name="confirmar" title="¿Confirmar compra?" description="…">
    …contenido…
    <x-slot:footer>…acciones…</x-slot:footer>
</x-modal>
<button x-on:click="$dispatch('open-modal', 'confirmar')">Confirmar</button>
<button x-on:click="$store.toasts.push('Guardado.', 'success')">Probar toast</button>
```

- Toast: éxito no crítico. Banner: advertencia persistente. Modal: solo decisión importante.
- Modal y hoja cierran con `Esc`, click en el fondo y botón de cierre.

## 5. Iconos

`<x-icon name="house" />` renderiza `data-lucide`; Lucide lo sustituye al cargar.

**Importante:** los íconos se importan explícitamente en `resources/js/app.js`. Al usar uno nuevo, agrégalo al `import` y al mapa de `createIcons`; si no, no se dibuja. Los nombres disponibles son los slugs de `node_modules/lucide/dist/esm/icons` (por ejemplo `triangle-exclamation-point`, no `alert-triangle`).

## 6. Estados obligatorios por pantalla

1. Inicial / carga (`<x-skeleton>`), con `role="status"` para lectores de pantalla.
2. Vacío con siguiente paso (`<x-empty-state>` + acción real).
3. Sin resultados de filtros.
4. Error recuperable (`<x-banner tone="danger">` + reintentar).
5. Éxito (`<x-banner tone="success">` o toast).
6. Confirmación para acciones sensibles.

## 7. Accesibilidad (WCAG 2.2 AA)

- Foco visible global: contorno de 3 px `ai` con offset 2 px.
- Objetivos táctiles de al menos 44 px (`min-h-11` / `size-11`).
- Skip link a `#contenido` al inicio del documento.
- `aria-current="page"` en navegación activa; `aria-disabled` en módulos no disponibles.
- `aria-live="polite"` en la región de toasts y contadores.
- `prefers-reduced-motion` desactiva animaciones.
- El modal no atrapa el foco todavía (pendiente EP-02); se recomienda no anidar modales.

## 8. Formularios de pocos pasos

Patrón para flujos como “registrar gasto” o “confirmar compra”:

1. Un objetivo por pantalla o sheet; máximo 3 pasos.
2. Paso 1 datos mínimos (monto, cuenta, concepto); paso 2 clasificación (categoría, visibilidad); paso 3 revisión y confirmación.
3. Barra de progreso del flujo con `<x-progress>` y texto “Paso 2 de 3”.
4. Validación server-side siempre; errores inline sin perder lo capturado.
5. Acción primaria fija al final; cancelar siempre visible.
6. En móvil, teclado numérico para importes (`money`), targets de 44 px y bottom sheet para selección.

## 9. Pendientes de EP-02

- `combobox` con búsqueda y `date picker` accesible.
- Componentes de tabla reutilizables (hoy la tabla vive en la vista de muestra).
- Paginación y filtros persistentes.
- Componentes de dominio: tarjeta de receta, producto, checkbox de compra, barra de presupuesto y progreso de meta (EP02-07), más los cinco prototipos de flujo (EP02-09).
- Focus trap real en modal/sheet.
