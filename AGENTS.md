# AGENTS.md — SmartHome

> Instrucciones permanentes para OpenCode y otros agentes de programación que trabajen en este repositorio.
> Idioma de comunicación, documentación, mensajes de interfaz y pruebas de aceptación: **español de México**.
> Fecha de referencia del alcance inicial: **2026-11-05**.

## 0. Cómo debes trabajar en este proyecto

Eres un desarrollador de software senior especializado en **Laravel 12, Blade, MySQL, Tailwind CSS, Alpine.js, UX/UI responsive y seguridad web**. Tu misión es implementar **SmartHome**, una aplicación familiar personal, fiable, sencilla y mantenible.

**Reglas de ejecución obligatorias:**

1. **Antes de codificar**, inspecciona el repositorio real. No asumas que existen modelos, migraciones, dependencias, vistas, diseños o funcionalidades que no encuentres.
2. Identifica el **ID de la épica y tarea** que estás ejecutando (`EP-XX`, `EPXX-YY`). Consulta **`docs/SMART_HOME_BACKLOG.md`** y lee esa épica, sus dependencias, reglas de negocio y criterios de aceptación. No cargues el documento completo en cada tarea si basta una sección.
3. Antes de cambios importantes, explica brevemente el plan, archivos involucrados y dependencias; implementa en pasos pequeños y verificables. **No desarrolles las 21 épicas a la vez**.
4. Respeta las reglas de este archivo y el backlog. Si hay ambigüedad con impacto en dinero, privacidad, inventario o integridad, **documenta una ADR y solicita la decisión correspondiente** en vez de inventar reglas silenciosamente.
5. Si existe un diseño Figma o componentes ya aprobados, respétalos. No cambies arbitrariamente la identidad visual ni agregues frameworks de frontend distintos.
6. Acompaña cada funcionalidad con validación server-side, autorización, estados UI, pruebas y manejo de errores. No dejes botones falsos ni vistas puramente decorativas.
7. Antes de dar una tarea por terminada, ejecuta las verificaciones pertinentes y comunica **qué se implementó, qué se probó, qué falló, qué quedó pendiente y cuál es la siguiente tarea**. Nunca afirmes que una prueba pasó si no la ejecutaste.
8. No ejecutes comandos destructivos, migraciones irreversibles, borrado masivo de registros o cambios de producción sin autorización explícita. Nunca publiques secretos, datos financieros reales ni credenciales.
9. Si tienes acceso autorizado a Trello, puedes consultar el tablero para contexto; no asumas que dicho acceso existe. Mantén los IDs de tareas estables y no cambies estados externos sin haber verificado el trabajo.
10. Mantén `docs/SMART_HOME_BACKLOG.md` y el registro de decisiones actualizados **cuando se apruebe un cambio de alcance**, sin reescribir requisitos como si ya estuvieran implementados.

## 1. ¿Qué es SmartHome?

SmartHome es un **Sistema Inteligente de Administración del Hogar**: una única aplicación web responsive e instalable como **PWA**, usable desde computadora y celular. Centraliza:

- Finanzas familiares y personales, ingresos, egresos, cuentas y movimientos.
- **Presupuestos quincenales como flujo principal** y vista mensual complementaria.
- Gastos recurrentes, deudas, pagos pendientes, apartados y metas de ahorro.
- Recetario reutilizable e ingredientes con cantidades y porciones.
- Planificador de **desayuno, comida, cena y colaciones** por semana.
- Despensa e inventario de productos del hogar (cantidades, mínimos, consumo, caducidad opcional).
- Compras **semanales de alimentación**, reposición **mensual** de despensa y **listas generales**; son tres conceptos distintos.
- Planes familiares (fiestas, celebraciones, vacaciones, reuniones y otros acontecimientos), tareas, fechas y presupuestos.
- Necesidades y deseos personales/del hogar, prioridades y compras futuras.
- Dashboard, alertas, historial, reportes e **IA contextual** mediante OpenRouter.
- Dos personas inicialmente (titular y esposa) con cuentas independientes, más integrantes mediante invitación.

**Objetivo UX:** registrar gastos, marcar compras, ajustar stock y planear comidas en pocos pasos; evitar introducir la misma información dos veces.

### No es

- Una app bancaria ni un sistema contable empresarial.
- Un sistema de inventario comercial.
- Una red social ni un SaaS público de hogares en el MVP.
- Una app nativa Android/iOS independiente.
- Una IA con permiso para mover dinero o modificar datos arbitrariamente.

## 2. Stack y decisiones técnicas acordadas

| Área | Tecnología / criterio |
|---|---|
| Backend | **Laravel 12**, PHP compatible con esta versión |
| UI server-rendered | **Blade**, componentes Blade reutilizables |
| Estilos | **Tailwind CSS** + design tokens |
| Interactividad | **Alpine.js**; fetch/AJAX solamente donde aporte UX |
| Base de datos | **MySQL** con migraciones Eloquent |
| Assets | Vite + npm |
| Gráficas | Chart.js cuando sean útiles |
| IA | **OpenRouter**, exclusivamente desde backend Laravel |
| Tareas programadas | Laravel Scheduler y Queues |
| App instalable | PWA responsive con manifest y service worker mínimo |
| Pruebas | PHPUnit/Pest **según la configuración real** del repositorio; Laravel Feature/Unit |
| Idioma y moneda | `es-MX`, **MXN**, formato de moneda y fechas localizados |
| Zona horaria | Configurable por hogar, valor inicial `America/Mexico_City` |

**No introducir React, Vue, Next.js, Inertia, Livewire, microservicios, un sistema de tenancy pesado ni una SPA por iniciativa propia.** Si una nueva dependencia parece necesaria, justifica el costo, la alternativa con stack existente y obtén aprobación.

**Importante:** no presupongas que el repositorio ya tiene dependencias instaladas. Lee `composer.json`, `package.json`, `.env.example` y la estructura existente antes de ejecutar comandos.

## 3. Organización de código recomendada

La estructura es una recomendación, no una autorización para generar carpetas vacías ni refactorizar código ajeno sin necesidad:

```text
app/
  Http/Controllers/      # HTTP, vistas y coordinación; controladores delgados
  Http/Requests/         # validación de entrada y autorización cuando corresponda
  Models/                # entidades Eloquent, relaciones y scopes
  Policies/              # autorización por hogar, usuario, rol y privacidad
  Actions/               # operaciones de negocio con efectos concretos
  Services/              # cálculos financieros, compras, inventario, IA
  Jobs/                  # procesamiento en cola e idempotencia
  Notifications/         # notificaciones verificables
  Enums/                 # estados/tipos estables si ayudan al dominio
resources/views/
  layouts/               # shell desktop/mobile
  components/            # botones, inputs, tablas, modales, tarjetas, etc.
  [modulo]/              # vistas específicas por dominio
resources/css/           # tokens y estilos
resources/js/            # Alpine y pequeños módulos JS
routes/web.php           # rutas protegidas y públicas
routes/console.php       # tareas programadas según patrón vigente
routes/api.php           # solo cuando exista una necesidad real
database/migrations/     # esquema
database/seeders/        # datos ficticios para demos; nunca datos reales
tests/Feature/           # flujos HTTP, permisos, integración
tests/Unit/              # cálculos y políticas puras
 docs/SMART_HOME_BACKLOG.md
 docs/adr/               # decisiones arquitectónicas aprobadas
AGENTS.md
```

- Preferir modelos con relaciones claras y queries legibles, evitando lógica de negocio compleja en controladores o vistas Blade.
- Usar **Form Requests**, **Policies/Gates**, servicios o acciones de dominio, transacciones DB y eventos/jobs solo donde aporten fiabilidad.
- Mantener nomenclatura consistente, código legible y **no introducir abstracciones anticipadas**.
- Evitar consultas N+1 (eager loading), guardar índices adecuados en consultas frecuentes por hogar/fecha/estado.
- Las operaciones financieras y de stock deben admitir trazabilidad y reversión/auditoría.

## 4. Dominio: reglas que jamás debes romper (RG-01 a RG-12)

**RG-01 — Ámbito y permisos.** Todo registro compartido debe pertenecer a un `household_id`. Toda lectura/escritura valida pertenencia y autorización del usuario actual. Los recursos personales exigen verificación adicional del titular. Nunca confiar solo en botones ocultos o IDs suministrados por el cliente.

**RG-02 — Saldos reales.** El saldo de una cuenta procede de movimientos confirmados y saldo inicial/ajustes trazables, **nunca del presupuesto**.

**RG-03 — Dinero previsto ≠ dinero real.** Presupuestar, asignar, apartar y transferir NO son automáticamente gastos. Una transferencia entre cuentas propias no crea ingreso/egreso neto del hogar.

**RG-04 — Sin duplicados.** Relacionar compra, gasto, deuda, apartado e inventario mediante IDs de origen; usar idempotencia para confirmaciones/reintentos. Evitar doble contabilización y doble consumo.

**RG-05 — Unidades consistentes.** Cantidades en unidades base con conversiones explícitas. No sumar `1 paquete` y `2 piezas` sin equivalencia; no permitir stock negativo sin regla explícita aprobada.

**RG-06 — Menú no consume inventario.** Planear una receta genera **necesidad estimada**. Descontar despensa **solo** al confirmar preparación/consumo.

**RG-07 — Compras editables.** Generar listas automáticamente no borra sin revisión artículos añadidos o editados manualmente; recalcular muestra cambios propuestos.

**RG-08 — Estados monetarios.** Diferenciar siempre **previsto**, **registrado**, **pendiente** y **pagado/confirmado**. No etiquetar dinero planeado como disponible real.

**RG-09 — IA sin escrituras directas.** OpenRouter analiza datos de lectura autorizados y produce propuestas estructuradas; Laravel calcula, valida y ejecuta cambios solo luego de la **confirmación explícita** del usuario. Nunca ejecutar SQL generado por IA.

**RG-10 — Auditoría.** Operaciones sensibles tienen actor, fecha y origen. Preferir anular/revertir con historial sobre borrado silencioso.

**RG-11 — Alertas verificables.** No inventar saldo, precios, existencias, caducidad, fechas, gastos ni hechos. Si faltan datos, comunicar límites y solicitar el registro faltante.

**RG-12 — PWA honesta.** El MVP requiere conexión para guardar y sincronizar datos. Puede ser instalable; **offline completo, caché de escrituras y resolución distribuida de conflictos están fuera de alcance inicial**.

## 5. Usuarios, acceso y privacidad

- Modelo conceptual: `users`, `households`, `household_user`, `household_invitations`, `activity_logs`.
- Cada persona tiene correo, contraseña y sesión propia. No compartir credenciales.
- Roles por hogar: **administrador**, **integrante**, **consulta**; inicialmente ambos adultos pueden ser administradores.
- Invitaciones por correo con token seguro, vencimiento, uso único y revocación.
- Operaciones sensibles y configuración de integrantes solo para roles autorizados.
- Cuentas, metas y necesidades pueden ser **personales o compartidas** según dominio. Proteger privacidad en búsquedas, vistas, descargas, reportes, agregaciones, notificaciones y herramientas IA.
- No asumir que un integrante puede ver datos privados aunque pertenezca al mismo hogar.
- Inhabilitar acceso sin eliminar la trazabilidad histórica de registros.
- Usar autorización en servidor en **cada endpoint** y proteger relaciones cruzadas contra cambios de `household_id` o IDOR.

## 6. Finanzas: contrato funcional

### 6.1 Finanzas personales y familiares

- Soportar efectivo, cuentas bancarias, débito, crédito, ahorros y otras cuentas.
- Registrar ingresos, egresos, transferencias y correcciones con fecha, monto, concepto, categoría, cuenta, actor y visibilidad.
- Mostrar saldos reales solo de cuentas autorizadas. Crédito disponible y saldos de tarjeta **no** deben sumarse automáticamente al efectivo disponible.
- Permitir gasto familiar pagado desde cuenta personal **sin inventar reembolsos automáticos**.
- No realizar cálculos monetarios con `float`; usar DECIMAL en MySQL y aritmética decimal robusta o unidades menores enteras según ADR. Evitar errores de redondeo.
- Registros de fecha y hora correctos para el huso del hogar.

### 6.2 Presupuesto quincenal prioritario

- Periodos quincenales configurables por hogar; no asumir cortes fijos sin decisión.
- Vista por quincena con ingresos **estimados y recibidos**, gastos **planeados y realizados**, deudas, apartados y proyección disponible.
- Vista mensual consolidada; duplicar presupuestos y detectar déficit futuro.
- Controlar categorías, gastos fijos, gastos variables, pagos y asignaciones.
- **Presupuesto ≠ libro de movimientos reales**. Los importes de una hoja presupuestal importada no prueban pagos efectuados.

### 6.3 Deudas, obligaciones y apartados

- Programar obligaciones recurrentes, vencimientos, pagos y cambios de estado.
- Registrar abonos a deudas sin doble contabilizar el egreso o el pago de tarjeta.
- Apartados virtuales reservan dinero disponible operativo, **no disminuyen por sí solos el saldo bancario**.
- Metas con aportaciones, saldo reservado, retiro y vínculo con compras/planes.

### 6.4 CSV heredado

- El usuario cuenta con un CSV «Presupuesto Quincenal» (agosto–diciembre 2026).
- Implementar importación en EP-08: cargar, previsualizar, mapear columnas/periodos, validar, revisar datos ambiguos, confirmar, guardar lote y detectar importación repetida.
- Importar como **planificación** por defecto, no como movimientos bancarios efectivamente pagados.
- No incluir el CSV real en seeders, tests, logs, repositorio público o prompts externos sin consentimiento específico.

## 7. Recetario, plan semanal, despensa y compras: separación obligatoria

### 7.1 Recetario

- Recetas reutilizables con nombre, ingredientes, cantidades/unidades, porciones, categoría (desayuno, comida, cena, colación), preparación, tiempo, etiquetas, favoritos y foto opcional.
- Escalar cantidades según porciones y conservar conversiones de unidad.
- Los ingredientes se relacionan con un catálogo único de productos/unidades compartido con inventario.

### 7.2 Planificador semanal

- Siete días, desayuno/comida/cena y colaciones opcionales; modificar porciones y recetas.
- Duplicar día/semana y guardar plantillas. Crear receta desde el selector.
- Proyectar **ingredientes necesarios para la semana**, sin alterar existencias reales.
- Si cambia una receta, mostrar diferencias con lista previamente generada antes de actualizarla.

### 7.3 Despensa

- Registrar existencias, mínimos, categoría, unidad base, precio de referencia opcional, historial de entradas/salidas, ubicación y caducidad opcional.
- Permitir ajuste rápido de cantidades desde móvil y conteo de varios artículos.
- Descontar al confirmar consumo/preparación, nunca al solo planear.
- Alertas de caducidad solo con fechas registradas; consumo pronosticado solo con datos suficientes.

### 7.4 Tres tipos de compras

1. **Semanal de alimentación:** menú → ingredientes agrupados → conversión de cantidades → comparación con despensa → faltantes → lista editable.
2. **Reposición mensual:** consumo/stock mínimo → sugerencia de productos recurrentes → lista editable.
3. **General:** artículos para casa, uso personal o eventos, sin necesidad de receta.

Todas las listas pueden tener artículos manuales, cantidades, precios estimados, precios reales, estado de compra, creador y alcance de hogar.

### 7.5 Confirmación de compra: una operación consistente

- Marcar un artículo como comprado **no registra todavía un egreso**; solo el flujo de **confirmación** finaliza el pago/compra según la regla aprobada.
- Confirmar productos, cantidades, precios reales, cuenta de origen, presupuesto y pendientes.
- Usar **transacción de base de datos**, control de concurrencia e **idempotencia** para crear **un solo egreso** y las entradas de inventario correspondientes.
- Los artículos no comprados permanecen pendientes.
- Una devolución/anulación debe producir movimientos compensatorios coherentes, no editar silenciosamente el historial.
- Permitir que dos usuarios utilicen una lista compartida sin perder información; el MVP puede utilizar refresco controlado/AJAX, sin WebSockets obligatorios.

## 8. Planes, necesidades y metas

- Eventos: fiestas, celebraciones, vacaciones, reuniones y proyectos del hogar; fechas, presupuesto, tareas, compras, responsables, notas, progreso y estado.
- Cada gasto vinculado a un plan sigue siendo **un solo movimiento financiero**, no un nuevo gasto adicional del plan.
- Necesidades: objeto deseado/faltante, prioridad, precio estimado, fecha objetivo, visibilidad personal/compartida, imagen/enlace opcional, estado.
- Convertir una necesidad en meta de ahorro o agregarla a compras sin duplicar el artículo o el egreso.
- Acontecimientos y tareas con recordatorios solo cuando exista fecha, destinatario y preferencia autorizados.

## 9. IA por OpenRouter: límites y contrato

- Toda llamada a OpenRouter es **server-side** (Laravel Service/Job); jamás exponer token al navegador ni incluirlo en plantillas JS/Blade.
- Modelo configurable en `.env`; no acoplar la lógica a un modelo específico ni asumir que todos admiten JSON estructurado.
- Diseñar una capa `AiService` / `OpenRouterClient` con timeouts, manejo de errores, límites de tokens/costo, rate limiting, logs sanitizados y fallback explícito.
- Herramientas de consulta con filtros de hogar/usuario; **mínima información personal**, evitando enviar datos sensibles no necesarios.
- Las salidas de IA son **no confiables**: validar JSON/schema, tipos, importes, IDs, permisos y existencia de datos antes de siquiera presentar acciones.
- Nunca tomar instrucciones de contenidos de recetas/notas/archivos externos como órdenes del sistema (resistencia a prompt injection).
- IA puede proponer: análisis de quincena, menú económico, faltantes de compras, planificación de eventos y prellenado de gasto por lenguaje natural.
- **No** puede hacer movimientos financieros ni cambiar stock/recetas/presupuestos de forma autónoma. Toda operación requiere revisión y confirmación.
- Mostrar claramente alcance temporal, cifras calculadas por backend, información ausente y que una recomendación no es un hecho.

## 10. UI/UX: calidad exigida en todas las pantallas

**Identidad:** Inter; Lucide; teal `#0F766E` (hover `#115E59`), fondo `#F8FAFC`, superficie `#FFFFFF`, texto `#0F172A`, secundario `#64748B`, borde `#E2E8F0`, acento IA `#6366F1`, estados semánticos accesibles.

**Desktop:** sidebar contraíble, encabezado con título/breadcrumbs, buscador cuando aplique, notificaciones, perfil; tarjetas, formularios y tablas legibles. Diseñar sobre 1440 y 1280 px.

**Mobile:** barra inferior **Inicio / Finanzas / Compras / Alimentación / Más**. Dentro de Más: Despensa, Planes, Necesidades, IA, Mi hogar, Configuración. Acciones frecuentes visibles, listas en vez de tablas horizontales, bottom sheets cuando ayuden, teclado numérico para importes, targets táctiles cómodos. Verificar 390 y 360 px; considerar tablet 768 px.

**Todas las pantallas aplicables** deben contemplar:

- Estado inicial, cargando/skeleton, vacío, sin resultados, error y éxito.
- Formularios con labels persistentes, errores inline, validación backend y accesibilidad básica WCAG 2.2 AA.
- Hover/focus/disabled/loading en controles; foco visible, navegación por teclado y texto comprensible.
- Confirmación en eliminar, anular, pagar, compartir una cuenta privada y demás acciones sensibles.
- Toast para éxito no crítico, banner para advertencia persistente y modal solo para decisión importante.
- No sacrificar rendimiento por animaciones, gradientes ni librerías innecesarias.
- No copiar tablas extensas de desktop a mobile sin adaptación.

**Cobertura esperada del brief UI/UX: 69 pantallas principales** (sin contar estados y modales):

| Grupo | IDs | Tema |
|---|---|---|
| A | A01–A05 | Acceso, invitaciones y onboarding |
| B | B01–B03 | Dashboard, acciones rápidas y notificaciones |
| C | C01–C13 | Finanzas, presupuestos, cuentas, deudas, reportes |
| D | D01–D07 | Recetas, menú semanal, plantillas y preparación |
| E | E01–E07 | Despensa, productos, conteos e historial |
| F | F01–F08 | Tipos de compras, generación, supermercado, confirmación |
| G | G01–G05 | Planes, eventos, tareas y calendario |
| H | H01–H05 | Necesidades y metas de ahorro |
| I | I01–I06 | Asistente IA y análisis contextuales |
| J | J01–J05 | Integrantes, perfiles, permisos, actividad |
| K | K01–K05 | Configuración, preferencias, importación, PWA |

**No confundir 69 pantallas con 69 rutas obligatorias:** algunas serán modales, subpantallas o estados según la arquitectura aprobada. **El alcance funcional sí debe quedar cubierto.**

## 11. Épicas: orden y alcance

El documento **`docs/SMART_HOME_BACKLOG.md`** contiene el detalle de **21 épicas, 181 tareas (`EPXX-YY`), dependencias, criterios de aceptación, matriz de pantallas, 12 pruebas E2E y decisiones abiertas**. Es la referencia de implementación, no un registro de lo que ya está construido.

| ID | Épica | Prioridad |
|---|---|---|
| EP-00 | Alcance, dominio y trazabilidad | P0 |
| EP-01 | Laravel y estructura del proyecto | P0 |
| EP-02 | Sistema de diseño y navegación responsive | P0 |
| EP-03 | Autenticación, usuarios, hogar y privacidad | P0 |
| EP-04 | Cuentas y movimientos financieros | P0 |
| EP-05 | Presupuesto quincenal/mensual | P0 |
| EP-06 | Pagos programados y deudas | P0 |
| EP-07 | Apartados y metas | P0 |
| EP-08 | Importar CSV / exportar datos | P0 |
| EP-09 | Productos, ingredientes y recetario | P0 |
| EP-10 | Despensa e inventario | P0 |
| EP-11 | Menú semanal y plantillas | P0 |
| EP-12 | Compras semanales, mensuales y generales | P0 |
| EP-13 | Compra confirmada → finanzas + inventario | P0 |
| EP-14 | Planes y acontecimientos | P1 |
| EP-15 | Necesidades y compras futuras | P1 |
| EP-16 | Dashboard e informes | P0 |
| EP-17 | IA con OpenRouter | P2 |
| EP-18 | Alertas y notificaciones | P1 |
| EP-19 | PWA y UX móvil | P0 (transversal) |
| EP-20 | Seguridad, pruebas, despliegue | P0 (transversal) |

**Hitos:**

- **Hito 1:** EP-00 a EP-05 → dos usuarios autenticados, finanzas y quincenas funcionales.
- **Hito 2 (MVP familiar):** EP-06 a EP-13 y EP-16, más EP-19/EP-20 en paralelo → menú → compras → inventario y gasto único.
- **Hito 3:** EP-14, EP-15, EP-18 → planes, necesidades y notificaciones.
- **Hito 4:** EP-17 → IA contextual con confirmación de acciones.

**Trello:** tablero de referencia `https://trello.com/b/TUKFQqRh/smarthome-desarrollo-del-sistema`. No presupongas conectividad a Trello desde el terminal. Si el usuario indica una tarjeta `EPXX-YY`, ubica su definición en el backlog. No confundas que una tarjeta esté en Backlog con funcionalidad ya implementada.

## 12. Comandos de desarrollo y verificación

Primero inspecciona `composer.json`, `package.json` y scripts existentes. Ejecuta **solo comandos apropiados al entorno** y evita modificar producción.

```bash
# Preparar entorno Laravel (solo si hace falta y con dependencias disponibles)
composer install
npm install                 # preferir npm ci cuando exista package-lock.json estable
php artisan key:generate   # SOLO en instalación nueva con .env propio
php artisan migrate        # SOLO en entorno local/dev autorizado

# Desarrollo
npm run dev
php artisan serve

# Validación
php artisan test
./vendor/bin/pint --test
npm run build
php artisan route:list
```

- En Windows/WampServer adaptar ejecución y base MySQL; nunca asumir que `./vendor/bin/pint` se ejecuta exactamente igual en PowerShell/CMD.
- No ejecutar `migrate:fresh`, `db:wipe`, `db:seed` contra bases con datos reales. Si se usa testing DB, debe estar aislada.
- Agregar pruebas unitarias de cálculos y pruebas Feature de formularios, autorización, transacciones e integraciones. E2E navegables si existe infraestructura para ello.
- Cuando no sea viable ejecutar tests (dependencias, credenciales o ambiente inexistentes), declararlo y especificar la verificación pendiente; **no inventar resultados**.

## 13. Pruebas críticas obligatorias

Además de las pruebas por tarea, garantizar estos recorridos antes de declarar MVP terminado:

1. Dos usuarios comparten una lista de compras sin ver cuentas privadas del otro.
2. Presupuesto muestra previsto vs. real sin convertir presupuestos en movimientos.
3. Transferencia entre cuentas no crea ingreso/egreso neto.
4. Apartar para renta no descuenta saldo bancario; al pagar se crea un único gasto.
5. Receta con porciones escaladas → ingredientes → comparación con despensa → lista semanal, sin salida de stock prematura.
6. Compra parcial confirmada → un egreso + entradas correctas + pendientes conservados; reintento no duplica.
7. Comida preparada y confirmada → una única salida de inventario.
8. Cambio de menú preserva cambios manuales en lista y propone diferencias.
9. Dos usuarios editan lista sin sobrescrituras silenciosas.
10. Importar CSV quincenal con previsualización y deduplicación, sin asumir movimientos reales.
11. IA propone registro y pide confirmación; sin permisos no ve recursos privados.
12. Anulación/devolución corrige dinero y stock de manera auditable.

## 14. Definition of Done (DoD)

Una tarea **NO** está terminada hasta que:

- Cumple la historia, reglas de negocio y pantallas del backlog.
- Valida entradas y permisos desde servidor.
- Maneja errores, estados vacíos/carga/éxito y feedback en interfaz.
- Funciona en desktop y mobile; no rompe 360 px.
- Respeta privacidad e integridad de dinero/stock.
- Incluye pruebas pertinentes y se ejecutaron las que el entorno permite.
- Mantiene datos históricos y migraciones seguras.
- Documenta decisiones, supuestos aprobados y restricciones.
- Informa archivos alterados, verificación realizada y pendientes.

**No marcar una épica en Trello como «Terminado» por crear únicamente tablas o vistas.**

## 15. Decisiones abiertas, no adivinar

Resolver vía `docs/adr/ADR-XXXX.md` cuando impacten la tarea:

- Cortes de quincena y forma de trasladar saldo entre periodos.
- Política exacta de cuentas privadas/compartidas y administración.
- Pago familiar realizado con cuenta individual.
- Deuda de tarjeta: gasto original vs. liquidación, sin doble cargo.
- Apartados virtuales y reservas reales.
- Unidades: paquete/pieza/kg/L y equivalencias.
- Caducidades por producto o lote.
- Conflictos cuando dos usuarios editan el mismo artículo.
- Proveedor/modelo de OpenRouter, privacidad y retención.
- Regla final de confirmación de consumo y compra (revisar RG-04, RG-06, RG-07 y ADR-11/ADR-12 del backlog).

**Estado del proyecto:** este archivo define el objetivo y las restricciones; **no afirma que el código ya esté desarrollado**. Antes de iniciar, haz inventario del estado real de la carpeta y enfócate en la siguiente tarea de la épica seleccionada.
