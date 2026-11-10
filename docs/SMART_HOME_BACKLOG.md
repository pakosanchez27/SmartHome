# SmartHome — Backlog maestro de épicas

**Versión:** 1.0 · **Fecha:** 5 de noviembre de 2026
**Proyecto:** SmartHome — Sistema Inteligente de Administración del Hogar
**Stack acordado:** Laravel 12, Blade, MySQL, Tailwind CSS, Alpine.js, Chart.js, OpenRouter, Scheduler/Queues y PWA.
**Usuarios iniciales:** dos integrantes adultos con cuentas separadas y un hogar compartido; arquitectura extensible a más integrantes.
**Idioma, moneda y calendario:** es-MX, MXN; periodos quincenales configurables, consultas mensuales y menú semanal.

> **Propósito del documento:** conservar los requisitos acordados y convertirlos en entregables implementables. Esta es la fuente de verdad funcional y de alcance. Cada cambio futuro deberá actualizar la épica, su criterio de aceptación y su referencia de pantalla.
>
> **Estado del proyecto:** este documento es el plan, **no** un registro de lo ya construido. Al 2026-11-05 no existe código de la aplicación en el repositorio.
>
> **Avance al 2026-11-06:** EP-00 y EP-01 terminados; EP-02 implementa el sistema de diseño y la biblioteca UI. EP-03 en progreso: EP03-01 a EP03-06 ya tienen código y pruebas (registro con alta de hogar; invitaciones por correo con token de un solo uso; roles `admin`/`member`/`viewer` con Policies; cambio de rol, desactivación reactivable y retiro definitivo de integrantes con `removed_at` en el pivot). Los frames A01–A05 del grupo de acceso y onboarding **no existen en el archivo Figma**; las pantallas de alta y del invitado son puentes con el lenguaje del design system, **pendientes de diseño oficial**. Las ADR siguen en estado Propuesto/Parcialmente aprobado (ADR-0003: ¿puede una administradora cambiar su propio rol o salir?).
>
> **Origen:** contenido sincronizado desde el tablero Trello «SmartHome | Desarrollo del Sistema» (`https://trello.com/b/TUKFQqRh/smarthome-desarrollo-del-sistema`).

## 1. Alcance y principios que no deben perderse

- Aplicación **web progresiva (PWA)** usable desde escritorio y celular, una única base de código; no desarrollar apps nativas por separado en la primera versión.
- **Dos usuarios iniciales** con credenciales distintas y posibilidad de invitar más. Cada dato pertenece a un hogar o a un usuario, con permisos verificables en servidor.
- Finanzas **personales y compartidas**. Los ingresos y gastos se registran en cuentas, no se confunden con presupuestos, reservas de dinero ni transferencias.
- Presupuesto **principalmente quincenal**, conservando los conceptos y la planificación por periodos del CSV existente. Vista mensual complementaria.
- **Despensa ≠ compras semanales ≠ reposición mensual**. Son conceptos distintos y relacionados.
- **Recetario ≠ menú semanal**. Una receta es reutilizable; el menú indica qué preparar en un día, fecha y porciones.
- La compra confirmada puede crear **un gasto y entradas de inventario** de manera consistente; editar una lista no significa gastar dinero.
- Seleccionar una receta para el menú **no descuenta stock**; el consumo se confirma al preparar o registrar el uso.
- IA por OpenRouter: **analiza, explica y propone**. Laravel calcula importes, valida propuestas y ejecuta escrituras **solo tras confirmación explícita**.
- UI **minimalista, profesional, responsive y UX-first**: Inter, Lucide; `#0F766E` primario, `#F8FAFC` fondo, `#0F172A` tinta, `#6366F1` IA; mensajes claros, componentes reutilizables.
- UX móvil orientada a registrar gastos, marcar compras, ajustar despensa y elegir comidas rápidamente.
- Datos de prueba y mockups con valores ficticios; el CSV es material de referencia **presupuestal**, no prueba de pagos reales.

### Reglas globales de negocio (RG)

| ID | Regla que nunca debe romperse |
|---|---|
| RG-01 | Toda consulta y escritura de recursos compartidos se acota por `household_id` y permisos; recursos privados, por titular autorizado. |
| RG-02 | Saldo de cuenta = movimientos confirmados y ajustes trazables; no se calcula a partir del presupuesto. |
| RG-03 | Presupuestar/asignar/apartar no implica necesariamente gastar; transferir entre cuentas no es ingreso ni egreso del hogar. |
| RG-04 | Gasto, compra, deuda, apartado y movimiento de inventario tienen IDs y orígenes para evitar doble contabilización. |
| RG-05 | Cantidades almacenadas en unidades base compatibles, con conversiones explícitas y sin stock negativo salvo excepción autorizada. |
| RG-06 | El menú solo reserva una **necesidad estimada**, no consume existencias; la preparación confirmada genera salidas. |
| RG-07 | La lista de compras puede generarse de recetas o reglas de reposición, y también editarse a mano; recalcularla no borra modificaciones sin revisión. |
| RG-08 | Se diferencia importe **previsto**, **registrado** y **pagado** en todo flujo financiero. |
| RG-09 | La IA jamás ejecuta SQL ni escribe datos por su cuenta; usa herramientas de lectura acotadas y propuestas estructuradas aprobadas. |
| RG-10 | Toda operación sensible permite auditoría y, cuando proceda, anulación/reversión en vez de borrado silencioso. |
| RG-11 | Las notificaciones dependen de hechos verificables y preferencias; no inventan disponibilidad, precios, caducidades ni recomendaciones sin datos. |
| RG-12 | PWA instalable con conectividad; el funcionamiento sin conexión y sincronización offline no forman parte obligatoria del MVP. |

## 2. Catálogo de épicas, dependencias y prioridad

**Prioridades:** P0 = indispensable para el primer MVP útil; P1 = siguiente entrega funcional; P2 = mejora avanzada. Las épicas transversales se ejecutan por incrementos en todos los hitos.

| Épica | Nombre | Prioridad | Depende de |
|---|---|---|---|
| EP-00 | Alcance, modelo de dominio y trazabilidad | P0 | — |
| EP-01 | Base técnica Laravel, datos y DevOps local | P0 | EP-00 |
| EP-02 | Design system, navegación y accesibilidad | P0 | EP-00, EP-01 |
| EP-03 | Autenticación, hogares, integrantes y privacidad | P0 | EP-01, EP-02 |
| EP-04 | Motor financiero, cuentas y movimientos | P0 | EP-03 |
| EP-05 | Presupuestos quincenales/mensuales | P0 | EP-04 |
| EP-06 | Pagos recurrentes y deudas | P0 | EP-04, EP-05 |
| EP-07 | Apartados y metas financieras | P0 | EP-04, EP-05 |
| EP-08 | Importación del CSV y exportación | P0 | EP-04–07 |
| EP-09 | Catálogo de ingredientes y recetario | P0 | EP-03 |
| EP-10 | Despensa e inventario doméstico | P0 | EP-03, EP-09 |
| EP-11 | Menú semanal y plantillas | P0 | EP-09, EP-10 |
| EP-12 | Generador y operación de listas de compras | P0 | EP-10, EP-11 |
| EP-13 | Confirmación de compra e integración de módulos | P0 | EP-04, EP-10, EP-12 |
| EP-14 | Planes, acontecimientos, tareas y calendario | P1 | EP-03, EP-04, EP-12 |
| EP-15 | Necesidades, deseos y adquisiciones | P1 | EP-07, EP-12, EP-14 |
| EP-16 | Dashboard, reportes y vistas agregadas | P0 | EP-04–05, EP-10–13 |
| EP-17 | Asistente IA con OpenRouter | P2 | EP-04–16 según capacidad |
| EP-18 | Alertas, notificaciones y Scheduler | P1 | EP-04–16 |
| EP-19 | PWA, UX móvil y resiliencia de interfaz | P0 | EP-02, módulos implementados |
| EP-20 | Seguridad, pruebas, despliegue y mantenimiento | P0 transversal | Todas según entrega |

> **Nota de gestión:** el tablero no debe obligar a completar todos los elementos P0 antes de probar el sistema. Se desarrollan incrementos verticales utilizables, aunque para considerar cerrado el MVP familiar deben superarse las pruebas de EP-20.

### Hitos

- **Hito 1:** EP-00 a EP-05 → dos usuarios autenticados, finanzas y quincenas funcionales.
- **Hito 2 (MVP familiar):** EP-06 a EP-13 y EP-16, más EP-19/EP-20 en paralelo → menú → compras → inventario y gasto único.
- **Hito 3:** EP-14, EP-15, EP-18 → planes, necesidades y notificaciones.
- **Hito 4:** EP-17 → IA contextual con confirmación de acciones.

---

## 3. Detalle por épica

### EP-00 — Alcance, arquitectura de dominio y trazabilidad · P0

**Dependencias:** — · **Estado inicial:** Backlog

**Objetivo:** establecer una especificación verificable para evitar interpretaciones contradictorias entre diseño, base de datos y desarrollo.

**Historias:** Como desarrollador quiero conocer reglas e interdependencias para implementarlas correctamente; como integrante quiero que el sistema no cambie la forma prevista de administrar el hogar.

**Tareas:**
- [x] EP00-01 Crear glosario: hogar, usuario, cuenta, saldo, ingreso, egreso, transferencia, presupuesto, apartado, deuda, receta, ingrediente, producto, menú, compra y plan.
- [x] EP00-02 Diagramar entidades y relaciones con campos de pertenencia/visibilidad (`household_id`, `created_by`, `owner_user_id` según modelo).
- [x] EP00-03 Documentar reglas RG-01 a RG-12 y casos de uso críticos.
- [x] EP00-04 Mantener matriz de requisitos ↔ pantallas ↔ historias ↔ pruebas.
- [x] EP00-05 Definir roles y niveles de privacidad, con administrador de hogar y usuarios participantes.
- [x] EP00-06 Registrar decisiones pendientes como ADR: día de corte quincenal, reglas de cuentas compartidas, representación de paquetes, tratamiento de importes históricos.
- [x] EP00-07 Definir alcance MVP vs. P1/P2, y convenciones de prioridad, nomenclatura y versionado.

**Criterios de aceptación:** ninguna entidad crítica carece de dueño/ámbito; presupuestos y movimientos tienen definiciones distintas; cada pantalla tiene una épica responsable y prueba prevista; los cambios de alcance se registran con fecha y motivo.

**Entregables:** mapa de dominio, matriz de trazabilidad, reglas de negocio, ADR y backlog aprobado.

---

### EP-01 — Base técnica, Laravel y estructura de proyecto · P0

**Dependencias:** EP-00 · **Estado inicial:** Backlog

**Avance:** base instalada y verificada el 2026-11-05. EP01-05 queda pendiente de las migraciones de dominio de EP-03/EP-04; la convención `DECIMAL(14,2)` ya está declarada en `config/smarthome.php`.

**Objetivo:** disponer de una aplicación mantenible preparada para funcionalidades familiares.

**Tareas:**
- [x] EP01-01 Crear proyecto Laravel 12, configurar PHP compatible, MySQL, Vite y entorno local.
- [x] EP01-02 Instalar/configurar Blade, Tailwind CSS, Alpine.js, Inter y Lucide.
- [x] EP01-03 Estructurar rutas, controllers, requests, services/actions, policies, jobs, notifications y tests.
- [x] EP01-04 Configurar autenticación segura, sesiones, CSRF, rate limiting, registro de errores y manejo uniforme de excepciones.
- [ ] EP01-05 Crear migraciones y seeders iniciales separados por módulo; definir moneda `DECIMAL(14,2)` y timestamps.
- [x] EP01-06 Preparar storage privado, subida segura de imágenes/comprobantes y política de backups.
- [x] EP01-07 Configurar colas, Scheduler, envío de correo transaccional, logs y variables de entorno.
- [x] EP01-08 Configurar repositorio Git, ramas, estándares de código, formateador y ejecución automática de pruebas.
- [x] EP01-09 Implementar detección de entorno y estructura de configuración para OpenRouter sin exponer API keys.

**Criterios:** instalación reproducible desde README; `migrate:fresh --seed` funcional; tests base pasan; ninguna clave en repositorio; errores no muestran trazas sensibles al usuario.

**Entregables:** repositorio inicial, README de instalación y base del proyecto.

---

### EP-02 — Sistema de diseño, navegación y UX responsive · P0

**Dependencias:** EP-00, EP-01 · **Estado inicial:** Backlog
**Pantallas vinculadas:** transversal; shell de B01 y navegación de todos los módulos.

**Avance 2026-11-05:** tokens, tipografía, layout desktop/móvil, biblioteca base de componentes y guía UI implementados sobre el archivo de Figma (`resources/css/app.css`, `resources/views/components/`, `resources/views/ui-kit.blade.php`, `docs/GUIA_UI.md`). Quedan combobox, date picker, paginación, componentes de dominio y prototipos de flujo.

**Objetivo:** establecer componentes Blade reutilizables que reproduzcan los mockups de diseño sin recargar la aplicación.

**Tareas:**
- [x] EP02-01 Convertir identidad a tokens Tailwind: `#0F766E`, `#115E59`, `#F8FAFC`, `#FFFFFF`, `#0F172A`, `#64748B`, `#E2E8F0`, `#6366F1`, estados semánticos.
- [x] EP02-02 Definir tipografía Inter, espaciado, radios, contraste AA, foco visible, tamaños táctiles y variantes de íconos.
- [x] EP02-03 Crear layout desktop: sidebar colapsable, breadcrumbs, título, acciones, búsqueda y perfil.
- [x] EP02-04 Crear layout móvil: barra inferior Inicio/Finanzas/Compras/Alimentación/Más, menús y acción rápida.
- [ ] EP02-05 Componentes Blade: button, input, money input, select, combobox, date picker, card KPI, badge, table, list card, tabs, modal, drawer, bottom sheet, toast, banner y confirmación. *(avance: button, input, money, select, card KPI, badge, list card, tabs, modal, bottom sheet, toast, banner y confirmación; faltan combobox, date picker, drawer y componente de tabla)*
- [ ] EP02-06 Estados vacíos, skeletons, errores inline, filtros, paginación y botones con progreso. *(avance: vacío, skeleton, error inline y loading; falta paginación)*
- [ ] EP02-07 Componentes de dominio: tarjeta receta, selector porciones, producto, control cantidad, checkbox compra, barra presupuesto, progreso de meta, movimiento financiero.
- [x] EP02-08 Documentar patrones de formularios de pocos pasos y responsive 360/390/768/1280/1440 px.
- [ ] EP02-09 Crear prototipos funcionales de los cinco flujos prioritarios: gasto, presupuesto, menú, compra y stock.

**Criterios:** todas las pantallas emplean componentes compartidos; móvil no contiene tablas horizontales impracticables; teclado, lectores de pantalla y mensajes de error usables; sin botones que parezcan activos y no hagan nada.

**Entregables:** biblioteca UI, layouts y guía de interacción.

---

### EP-03 — Identidad, hogares, usuarios y permisos · P0

**Dependencias:** EP-01, EP-02 · **Estado inicial:** Backlog
**Pantallas:** A01–A05; J01–J05; partes de K01/K03.

**Objetivo:** dos cuentas independientes con un hogar compartido y posibilidad real de invitar a más integrantes.

**Tareas:**
- [x] EP03-01 Login, recuperación de contraseña, edición de perfil y cierre de sesión.<!-- 2026-11-05 -->
- [x] EP03-02 Entidades `households`, `users`, `household_user`, invitaciones con token de un solo uso y caducidad.<!-- 2026-11-06: invitaciones con hash SHA-256, vigencia de 7 días (J02) y estados pending/accepted/rejected/revoked -->
- [x] EP03-03 Alta inicial de hogar: nombre, MXN, zona horaria, quincenas y primer administrador.<!-- 2026-11-06: registro+hogar en una transacción; pantalla provisional por falta de frames A01–A05 en Figma; día de corte fijo en 15 según ADR-0001 pendiente -->
- [x] EP03-04 Invitaciones por correo, aceptación, rechazo, caducidad y revocación.<!-- 2026-11-06: solo admin invita (ROLES_Y_PRIVACIDAD); aceptación exige sesión con el correo invitado; reenviar renueva el token y anula el anterior; la página del invitado es puente (sin frame en Figma); reenvío sin límite de tasa → revisar en EP-20 -->
- [x] EP03-05 Roles: administrador, integrante y consulta. Permisos server-side mediante Policies/Gates.<!-- 2026-11-06: HouseholdPolicy (view/operate/manageMembers/update) y HouseholdInvitationPolicy (resend/cancel/accept); solo administradora invita, reenvía o cancela; aceptación = identidad por correo (el estado lo decide el claim idempotente con mensaje amable); matriz de roles e IDOR cruzado probados -->
- [x] EP03-06 Permitir dos administradores del hogar, invitados adicionales y cambio/revocación de roles.<!-- 2026-11-06: cambio de rol, desactivar (reactivable sin invitación) y retirar (definitivo; la única vía de vuelta es nueva invitación) según mockup J01; pivot con removed_at; solo admin gestiona (Policy); sin auto-gestión (cambiar propio rol/retirarse) y última administradora protegida — decisión para ADR-0003; 404 a IDs ajenos para evitar enumeración; Reeenvío admitido al retirado por nueva invitación -->
- [x] EP03-07 Soportar recursos privados y compartidos (particularmente cuentas y necesidades); evitar filtraciones en listados, búsquedas, reportes, notificaciones y IA.<!-- 2026-11-10: contrato reutilizable `owner_user_id` + `shared_at` + `household_id` mediante Visibility/HasOwnerVisibility; el alcance exige hogar y membresía activa, y las pruebas cubren privados, compartidos, hogar ajeno y membresías inactivas. EP-04+ y EP-15 deberán aplicarlo a sus modelos, Policies, agregados, búsquedas, reportes, notificaciones e IA. -->
- [x] EP03-08 Registrar `created_by`, editor y eventos de auditoría de acciones sensibles.<!-- 2026-11-10: `activity_logs` inmutable registra hogar, actor, acción, entidad, fecha, origen y metadatos sanitizados; se auditan invitaciones y cambios de integrantes. `created_by` ya identifica al creador del hogar; EP-04+ añadirá autor/editor al crear sus recursos y usará ActivityLogger para operaciones sensibles. -->
- [x] EP03-09 UI de Mi hogar, integrantes, permisos, historial de actividad y estado de invitaciones.<!-- 2026-11-10: frames J01, J04 y J05 consultados en Figma; integrantes, invitaciones, permisos/privacidad y actividad son pestañas funcionales. Actividad filtra por rol, actor y fecha, sin exponer recursos privados; Mi perfil enlaza a su flujo existente. -->

**Criterios:** ambos usuarios pueden operar lista compartida; una cuenta privada no aparece ni por ID directo ni en agregados del otro; invitaciones no son reutilizables; desactivar usuario impide nuevas operaciones sin borrar su histórico; un usuario con rol consulta no modifica datos.

**Entregables:** módulo de acceso y hogar multiusuario seguro.

---

### EP-04 — Núcleo financiero: cuentas y movimientos · P0

**Dependencias:** EP-03 · **Estado inicial:** Backlog
**Pantallas:** C01, C04, C05, C06, C07, C08, C13.

**Objetivo:** tener una fuente de verdad financiera independiente del presupuesto.

**Tareas:**
- [ ] EP04-01 Entidades `accounts`, `categories`, `transactions`, `transaction_lines` o equivalente transaccional, y referencias a origen.
- [ ] EP04-02 Tipos de cuenta: efectivo, banco, débito, crédito, ahorro, otra; definir cómo se muestran pasivos y límites de crédito sin inflar disponible.
- [ ] EP04-03 Saldo inicial explícito y movimientos posteriores; dinero con `DECIMAL`, sin floats.
- [ ] EP04-04 CRUD de movimientos: ingreso, gasto y transferencia; validación de cuenta, importe positivo, categoría, ámbito y fecha.
- [ ] EP04-05 Diferenciar movimientos confirmados, programados/anulados y notas; no contar dos veces transferencias.
- [ ] EP04-06 Historial con filtros por rango, categoría, cuenta, usuario y ámbito; diseño desktop/móvil.
- [ ] EP04-07 Detalle, editar según política y anular mediante contramovimiento/auditoría.
- [ ] EP04-08 Crear categorías del usuario/hogar (alimentación, renta, luz, internet, transporte, salud, etc.).
- [ ] EP04-09 Registro rápido desde móvil, adjuntos opcionales, avisos de posible duplicidad.
- [ ] EP04-10 Pruebas de saldos y visibilidad, dos usuarios concurrentes y movimientos cruzados.

**Criterios:** cada gasto aparece una sola vez; transferir entre cuentas no altera ingresos/egresos agregados; saldos se reproducen desde histórico; no se accede a cuentas ajenas privadas; anulación deja trazabilidad.

**Entregables:** cuentas, ledger, categorías y movimientos reales.

---

### EP-05 — Presupuesto quincenal/mensual · P0

**Dependencias:** EP-04 · **Estado inicial:** Backlog
**Pantallas:** C02, C03, parte C01 y C12.

**Objetivo:** reemplazar la planificación manual por un presupuesto dinámico conservando la estructura del CSV.

**Tareas:**
- [ ] EP05-01 Modelar `budget_periods`, `budgets`, `budget_lines`, categorías e importes previstos por periodo.
- [ ] EP05-02 Configurar fechas de corte quincenal por hogar; admitir vista mensual sin fusionar indebidamente periodos.
- [ ] EP05-03 Capturar ingresos esperados, gastos fijos, variables, pagos de deuda y apartados previstos.
- [ ] EP05-04 Cálculo: remanente antes de asignaciones, total asignado, saldo sin asignar y déficit proyectado.
- [ ] EP05-05 Comparación de monto previsto vs. gasto/ingreso realmente confirmado, por categoría y periodo.
- [ ] EP05-06 Alta, edición, duplicación de la quincena anterior y proyecciones de quincenas futuras.
- [ ] EP05-07 Estados: borrador, planeado, activo, cerrado; reglas de edición de periodos cerrados.
- [ ] EP05-08 Filtros personal/familiar, sin mezclar recursos privados no autorizados.
- [ ] EP05-09 Indicadores y banners de déficit antes de guardar o asignar fondos.
- [ ] EP05-10 Pruebas con periodos de ingresos distintos, gastos imprevistos y quincenas con saldo negativo.

**Criterios:** cambios de importe esperado no alteran movimientos reales; gasto registrado afecta ejecución del presupuesto solo cuando corresponde a su periodo/categoría; cada quincena conserva historia; déficit visible y no maquillado; sumas de periodos coinciden con consolidado.

**Entregables:** planificador quincenal y resumen mensual verificables.

---

### EP-06 — Pagos programados y deudas · P0

**Dependencias:** EP-04, EP-05 · **Estado inicial:** Backlog
**Pantallas:** C09, C10.

**Objetivo:** programar compromisos sin volver a capturar el mismo pago en cada quincena.

**Tareas:**
- [ ] EP06-01 Modelar pagos recurrentes: nombre, importe, frecuencia, próxima fecha, vigencia y cuenta sugerida.
- [ ] EP06-02 Generar ocurrencias futuras sin crear automáticamente egresos confirmados.
- [ ] EP06-03 Flujo «Marcar como pagado» conectado a EP-04 y al presupuesto correcto.
- [ ] EP06-04 Modelar deudas: acreedor, monto, saldo, vencimientos, intereses opcionales si se conocen, estado e historial.
- [ ] EP06-05 Abonos parciales, pagos completos, saldos y calendario de próximos pagos.
- [ ] EP06-06 Detectar potencial duplicidad entre «pago de préstamo», otras deudas y líneas presupuestales.
- [ ] EP06-07 Permitir reprogramar/posponer sin fingir pago y auditar modificaciones.
- [ ] EP06-08 Avisos por vencimiento consumidos por EP-18.

**Criterios:** programar no descuenta el saldo; confirmar genera un único pago; abono parcial reduce la deuda según reglas; no hay contabilidad duplicada entre cuotas y deudas; fechas de próxima ocurrencia correctas.

**Entregables:** calendario de pagos y módulo de deudas.

---

### EP-07 — Apartados de dinero, ahorro y metas · P0

**Dependencias:** EP-04, EP-05 · **Estado inicial:** Backlog
**Pantallas:** C11, H04, H05.

**Objetivo:** administrar compromisos futuros sin confundir reservar dinero con gastarlo.

**Tareas:**
- [ ] EP07-01 Modelar fondos/apartados con objetivo, importe requerido, fecha meta, ámbito y disponibilidad.
- [ ] EP07-02 Crear apartados para renta, emergencia, compra de muebles, bebé o vacaciones.
- [ ] EP07-03 Registrar aportaciones y retiros; definir si cada fondo es virtual o corresponde a una cuenta de ahorro real.
- [ ] EP07-04 Calcular disponible operativo neto de fondos reservados sin duplicar disminuciones del saldo contable.
- [ ] EP07-05 Relacionar fondos con presupuestos y futuras necesidades/planes.
- [ ] EP07-06 Estados de meta: en progreso, completada, pausada; alertas por atraso solo si hay datos suficientes.
- [ ] EP07-07 Visualizar objetivo, acumulado, restante y progreso por periodo.

**Criterios:** una aportación a un apartado virtual no es gasto; retirar apartado no es ingreso; transferir a cuenta de ahorro real sigue siendo transferencia; cada presupuesto refleja asignaciones sin duplicarlas.

**Entregables:** fondos, apartados y metas reutilizables.

---

### EP-08 — Importación de presupuesto existente y exportaciones · P0

**Dependencias:** EP-04–07 · **Estado inicial:** Backlog
**Pantallas:** K04.
**Referencia real:** `Presupuestos - Presupuesto Quincenal.csv`, 55 filas; diez periodos de agosto a diciembre de 2026. Contiene ingresos, gastos fijos, deudas, asignación de remanente, totales, notas y anotaciones extra fuera del cuadro principal.

**Objetivo:** preservar la hoja previa y permitir transición ordenada a SmartHome.

**Tareas:**
- [ ] EP08-01 Implementar lectura CSV UTF-8/BOM, preview y detección de columnas/periodos.
- [ ] EP08-02 Mapeo de conceptos a categorías, cuentas, pagos recurrentes, deudas, metas o apartados.
- [ ] EP08-03 Importar **montos como datos planificados por defecto**, nunca asumir que fueron efectivamente pagados.
- [ ] EP08-04 Mantener fuente, fecha, fila y valor original para auditar importación.
- [ ] EP08-05 Revisar conflictos: por ejemplo «Pago de préstamo» en fijos y otras deudas, «Kueski» en asignaciones, «Reserva para renta» que no es gasto.
- [ ] EP08-06 Revisar observaciones incompatibles con cálculos o fechas; no inventar correcciones.
- [ ] EP08-07 Permitir selección de periodos, revisión de duplicados, corrección de categorías y operación de prueba sin guardar.
- [ ] EP08-08 Guardar importación idempotente o con aviso inequívoco de duplicidad y resumen de éxito/errores.
- [ ] EP08-09 Exportar presupuestos/movimientos/stock a CSV y, si se implementa, PDF.
- [ ] EP08-10 Documentar decisiones de conversión del CSV a entidades de dominio.

**Criterios:** al importar varias veces no aparecen periodos duplicados sin aviso; los totales del archivo se pueden cotejar con la vista previa; el origen preserva su significado; notas que requieren criterio quedan marcadas para revisión, nunca interpretadas en silencio.

**Entregables:** asistente de importación, reporte de mapeo y exportación.

---

### EP-09 — Catálogo de productos, ingredientes y recetario · P0

**Dependencias:** EP-03 · **Estado inicial:** Backlog
**Pantallas:** D01, D02, D03, D05.

**Objetivo:** tener recetas reutilizables ligadas a productos de inventario sin capturar ingredientes duplicados.

**Tareas:**
- [ ] EP09-01 Entidad unificada de productos/ingredientes con identificador por hogar, categoría, unidad base y aliases.
- [ ] EP09-02 Normalización y conversiones compatibles (g ↔ kg, ml ↔ L, piezas; paquetes con equivalencia declarada).
- [ ] EP09-03 Recetas: nombre, descripción, categoría, tiempo, porciones base, instrucciones, foto opcional, etiquetas y favorita.
- [ ] EP09-04 Relación receta–ingrediente con cantidades, unidades y notas; selector de productos existente.
- [ ] EP09-05 CRUD de recetas; duplicar, filtrar, buscar, favoritos y selector modal/bottom sheet.
- [ ] EP09-06 Ajuste matemático de ingredientes por número de porciones; criterios para redondeo y cantidades indivisibles.
- [ ] EP09-07 Costo estimado de receta basado en precios disponibles, marcando datos faltantes.
- [ ] EP09-08 Controles de duplicados sin bloquear legítimas variantes de receta.

**Criterios:** una receta para 4 porciones recalcula ingredientes para 2/6; un ingrediente registrado se puede usar en varias recetas; no se inventan precios; ingredientes con unidades incompatibles requieren corrección.

**Entregables:** biblioteca de recetas y catálogo de ingredientes/productos.

---

### EP-10 — Despensa e inventario · P0

**Dependencias:** EP-03, EP-09 · **Estado inicial:** Backlog
**Pantallas:** E01–E07.

**Objetivo:** conocer existencias reales o estimadas y mantener trazabilidad de entradas, consumos y ajustes.

**Tareas:**
- [ ] EP10-01 Modelar existencias por producto/hogar y opcionalmente ubicación, lote y fecha de caducidad.
- [ ] EP10-02 Movimientos de inventario: entrada, salida, ajuste, merma, corrección, referenciados a su origen.
- [ ] EP10-03 CRUD de productos y límites mínimos; filtros por categoría, stock bajo y agotados.
- [ ] EP10-04 Acciones rápidas móvil: sumar, restar y establecer cantidad exacta.
- [ ] EP10-05 Conteo rápido de varios productos con resumen previo a confirmar.
- [ ] EP10-06 Historial de movimientos, autor, fecha y causa.
- [ ] EP10-07 Indicadores de caducidad solo si hay fecha/lote capturado.
- [ ] EP10-08 Estimación futura de consumo basada en historial, sin exigir registro manual de cada gramo para operar.
- [ ] EP10-09 Prevención de decrementos concurrentes que produzcan stock negativo no permitido.

**Criterios:** guardar un ajuste deja movimiento trazable; dos integrantes pueden actualizar el mismo producto sin pérdidas silenciosas; caducidades desconocidas no generan alertas falsas; stock bajo se calcula respecto del mínimo definido.

**Entregables:** inventario de despensa funcional en PC y móvil.

---

### EP-11 — Planificación de comidas y plantillas semanales · P0

**Dependencias:** EP-09, EP-10 · **Estado inicial:** Backlog
**Pantallas:** D04, D06, D07.

**Objetivo:** organizar desayunos/comidas/cenas/colaciones con recetas seleccionadas y porciones específicas.

**Tareas:**
- [ ] EP11-01 Entidades `meal_plans` y `planned_meals`: semana, día, tipo de comida, receta, porciones, estado.
- [ ] EP11-02 Calendario desktop de siete días y editor móvil por día/semana.
- [ ] EP11-03 Añadir, cambiar, repetir, quitar y duplicar comidas/días/semanas.
- [ ] EP11-04 Guardar y aplicar plantillas completas de menú semanal.
- [ ] EP11-05 Mostrar costo semanal estimado y comparación con presupuesto de alimentación.
- [ ] EP11-06 Calcular necesidades agregadas de ingredientes con unidades normalizadas.
- [ ] EP11-07 Confirmar «comida preparada», editar cantidades usadas y generar salidas de inventario.
- [ ] EP11-08 Estado de menú incompleto y alertas cuando un cambio afecte listas de compras ya generadas.

**Criterios:** añadir receta al menú no descuenta stock; cambiar porciones modifica necesidades futuras; confirmar preparación solo descuenta una vez; menú se puede duplicar sin sobrescribir el existente.

**Entregables:** calendario familiar y reutilización de menús.

---

### EP-12 — Listas de compras semanales, mensuales y generales · P0

**Dependencias:** EP-10, EP-11 · **Estado inicial:** Backlog
**Pantallas:** F01, F02, F03, F04, F05, F07, F08.

**Objetivo:** administrar listas independientes y calcular faltantes desde recetas o stock.

**Tareas:**
- [ ] EP12-01 Entidades listas y líneas con tipo `semanal`, `reposicion_mensual`, `general`; estados borrador, pendiente, en proceso, completada, cancelada.
- [ ] EP12-02 Generador semanal: sumar ingredientes por receta/porciones y descontar existencia utilizable/comprometida cuando proceda.
- [ ] EP12-03 Generador mensual: mínimos, historial y horizonte de reposición; no depende de menú semanal.
- [ ] EP12-04 Edición manual: agregar/quitar productos, cambiar cantidades, marcar como opcionales, notas y orden por categoría.
- [ ] EP12-05 Costos estimados desde precio registrado; avisar de valores desconocidos.
- [ ] EP12-06 «Modo supermercado» móvil: checks, cantidad real, precio real, progreso y total acumulado.
- [ ] EP12-07 Control multiusuario de listas: actualización al refrescar/consultar, evitar que cambios concurrentes se sobrescriban.
- [ ] EP12-08 Regeneración no destructiva: comparar diferencias y preservar ajustes manuales.
- [ ] EP12-09 Historial de compras y comparación con precios históricos ingresados; sin prometer precios de tiendas en tiempo real.
- [ ] EP12-10 Separar claramente «marcar en carrito» de «confirmar compra/pago».

**Criterios:** semana y reposición mensual no se mezclan automáticamente; el usuario puede añadir jabón a lista semanal sin receta; una lista editada no se pierde al recalcular; checks todavía no generan egreso; dos usuarios ven la misma lista con cambios consistentes.

**Entregables:** generadores de listas y modo tienda usable en celular.

---

### EP-13 — Confirmación de compra: finanzas + inventario · P0

**Dependencias:** EP-04, EP-10, EP-12 · **Estado inicial:** Backlog
**Pantallas:** F06; integra C04/C05, E06 y F05.

**Objetivo:** que una compra confirmada actualice todos los módulos afectados exactamente una vez.

**Tareas:**
- [ ] EP13-01 Resumen de compra: elementos adquiridos, cantidades reales, precios reales, pendientes, cuenta de pago y categoría.
- [ ] EP13-02 Servicio transaccional Laravel que cree compra, gasto y entradas de stock de forma atómica.
- [ ] EP13-03 Distinguir compra de consumibles (entrada a stock) vs. servicio o artículo no inventariable (solo egreso).
- [ ] EP13-04 Asignar una compra a presupuesto quincenal/categoría y opcionalmente plan o necesidad.
- [ ] EP13-05 Mantener artículos no adquiridos como pendientes; soportar compras parciales y múltiples visitas a tienda.
- [ ] EP13-06 Llave de idempotencia/bloqueo contra doble tap, reintentos y confirmaciones simultáneas.
- [ ] EP13-07 Correcciones y devoluciones mediante reversos trazables de dinero y stock, con permisos.
- [ ] EP13-08 Auditoría de comprador, cuenta utilizada, precio registrado y origen de cada movimiento.
- [ ] EP13-09 Validar consistencia de stocks, total cobrado y suma de partidas.

**Criterios:** una misma compra no se registra dos veces; si falla un paso no quedan pagos ni stock a medias; compra parcial no elimina pendientes; corregir pago/stock mantiene trazabilidad; presupuesto refleja un gasto real y no una reserva.

**Entregables:** integración transaccional de los módulos críticos.

---

### EP-14 — Planes, eventos, tareas y calendario familiar · P1

**Dependencias:** EP-03, EP-04, EP-12 · **Estado inicial:** Backlog
**Pantallas:** G01–G05.

**Objetivo:** organizar celebraciones, viajes y acontecimientos, con presupuestos y pendientes.

**Tareas:**
- [ ] EP14-01 CRUD de planes con tipo, descripción, fechas, participantes, responsable, visibilidad y estado.
- [ ] EP14-02 Vista de plan con pestañas: resumen, presupuesto, tareas, compras, notas.
- [ ] EP14-03 Tareas con título, responsable, vencimiento, prioridad y estado.
- [ ] EP14-04 Vincular presupuesto, movimientos, apartados y listas de compras al plan, sin duplicar pagos.
- [ ] EP14-05 Calendario mensual desktop y agenda móvil con eventos/vencimientos.
- [ ] EP14-06 Plantillas futuras opcionales para cumpleaños/vacaciones sin sobreingeniería inicial.
- [ ] EP14-07 Mostrar avance, gasto real, presupuesto restante y tareas atrasadas.

**Criterios:** las tareas son editables por integrantes autorizados; una compra vinculada aparece en finanzas una sola vez; calendario refleja cambios; planes privados solo visibles a quienes tienen permiso.

**Entregables:** módulo de planeación familiar colaborativa.

---

### EP-15 — Necesidades, deseos y compras pendientes del hogar · P1

**Dependencias:** EP-07, EP-12, EP-14 · **Estado inicial:** Backlog
**Pantallas:** H01–H03; conexión H04/H05.

**Objetivo:** priorizar artículos personales/familiares y convertir necesidades en metas o compras.

**Tareas:**
- [ ] EP15-01 Catálogo de necesidades: nombre, categoría, prioridad, importe estimado, fecha objetivo, foto/enlace opcionales y ámbito.
- [ ] EP15-02 Estados: pendiente, planeada, en compra, adquirida, pospuesta/cancelada.
- [ ] EP15-03 Vista de lista por prioridad, importe, categoría y titularidad.
- [ ] EP15-04 Convertir necesidad a meta de ahorro vinculada (EP-07).
- [ ] EP15-05 Enviar necesidad a lista general o compra específica (EP-12), manteniendo origen.
- [ ] EP15-06 Marcar adquirida solo después de confirmar compra o mediante acción explícita con registro.
- [ ] EP15-07 Mostrar gasto real asociado y diferencia con estimación si existe.

**Criterios:** no duplicar gasto al completar una necesidad; se distinguen deseos, prioridades y compras reales; necesidades personales no se exponen en el hogar compartido sin autorización.

**Entregables:** lista de adquisiciones priorizadas e integrada con metas y compras.

---

### EP-16 — Dashboard e informes · P0

**Dependencias:** EP-04–05, EP-10–13 · **Estado inicial:** Backlog
**Pantallas:** B01, C01, C12 y resúmenes contextuales de cada módulo.

**Objetivo:** ofrecer respuestas rápidas sobre el estado del hogar desde PC y móvil.

**Tareas:**
- [ ] EP16-01 Dashboard con saludo, periodo, saldos autorizados y disponible tras compromisos.
- [ ] EP16-02 Tarjetas de ingreso/gasto, presupuesto, próximas obligaciones y apartados.
- [ ] EP16-03 Comida del día, progreso del menú, productos faltantes y lista semanal.
- [ ] EP16-04 Stock bajo, próximos eventos, tareas y metas relevantes.
- [ ] EP16-05 Gráficas Chart.js: presupuestado/real, por categoría, evolución, metas.
- [ ] EP16-06 Filtros de periodo, cuenta, categoría y personal/familiar respetando permisos.
- [ ] EP16-07 Acciones rápidas desktop/móvil: gasto, ingreso, compra, receta, stock y necesidad.
- [ ] EP16-08 Estados vacíos y datos insuficientes sin inventar saldo ni proyecciones.

**Criterios:** los totales coinciden con fuentes del módulo, no con sumas calculadas de forma distinta; al cambiar de ámbito no hay filtraciones; dashboard móvil prioriza efectivo disponible, comida hoy y compras.

**Entregables:** inicio familiar y reportes comprensibles.

---

### EP-17 — Inteligencia artificial con OpenRouter · P2

**Dependencias:** EP-04–16 según capacidad · **Estado inicial:** Backlog
**Pantallas:** I01–I06; superficies IA en B01, C02, D04 y F02.

**Objetivo:** asistente contextual útil que analice datos autorizados y proponga acciones revisables.

**Tareas:**
- [ ] EP17-01 Integrar OpenRouter con API key solo backend, timeout, retries acotados, logging seguro y presupuestos de uso.
- [ ] EP17-02 Diseñar capa de herramientas de **solo lectura** con alcance y autorización por hogar/usuario.
- [ ] EP17-03 Conversación con historial, sugerencias de prompts, estados de procesamiento/error y períodos de consulta visibles.
- [ ] EP17-04 Analista financiero: variaciones, compromisos, proyección y explicación basada en importes calculados por Laravel.
- [ ] EP17-05 Asesor alimentario: propuesta editable de menús según recetas, inventario, porciones y presupuesto.
- [ ] EP17-06 Recomendaciones de compras/despensa con indicación de datos desconocidos y confianza en la estimación.
- [ ] EP17-07 Planificador de eventos y estimador orientativo de necesidades, con presupuestos reales validados por servidor.
- [ ] EP17-08 Captura en lenguaje natural de gastos: extraer JSON validado, completar campos faltantes y mostrar confirmación.
- [ ] EP17-09 Flujo de propuestas: preview → usuario confirma → acciones Laravel autorizadas; nunca ejecución autónoma.
- [ ] EP17-10 Historial de sugerencias aplicadas/descartadas, retención, opt-outs y configuración de privacidad.
- [ ] EP17-11 Defensas contra prompt injection desde notas/recetas y pruebas de aislamiento de datos privados.

**Criterios:** si IA falla, los módulos principales siguen funcionando; respuesta no puede consultar cuentas privadas ajenas; no modifica saldos sin confirmación; importes calculados coinciden con backend; se ve de dónde proviene el consejo.

**Entregables:** centro IA y asistentes contextuales por módulo.

---

### EP-18 — Notificaciones, alertas y tareas programadas · P1

**Dependencias:** EP-04–16 · **Estado inicial:** Backlog
**Pantallas:** B03, K02; banners y toasts transversales.

**Objetivo:** mantener a la familia informada sin sobrecargarla de avisos.

**Tareas:**
- [ ] EP18-01 Centro de notificaciones con leído/no leído, filtros por módulo y marca múltiple.
- [ ] EP18-02 Scheduler/Jobs de pagos próximos, presupuesto cercano al límite, déficit previsto, stock bajo y eventos.
- [ ] EP18-03 Alertas de caducidad solo si fechas verificables están registradas.
- [ ] EP18-04 Distinción toast/banner/inline/modal para diferentes niveles de importancia.
- [ ] EP18-05 Preferencias por usuario y canal (in-app, correo opcional); evitar avisos reiterados.
- [ ] EP18-06 Respetar permisos en texto y enlaces de notificación.
- [ ] EP18-07 Estados de fallo y reintento sin duplicar avisos.

**Criterios:** no se envían notificaciones sobre datos privados de otro usuario; no hay alertas de caducidad inventadas; recordatorios no se duplican por reintentos de Jobs; usuario puede desactivar los no esenciales.

**Entregables:** centro de avisos y recordatorios programados.

---

### EP-19 — PWA, UX móvil y conectividad · P0

**Dependencias:** EP-02 y módulos implementados · **Estado inicial:** Backlog
**Pantallas:** K05 y comportamientos responsive de todas las pantallas.

**Objetivo:** hacer SmartHome cómodo para usar en supermercado y hogar, además del escritorio.

**Tareas:**
- [ ] EP19-01 Manifest, iconos, colores, instalación y requisitos de PWA.
- [ ] EP19-02 Revisar en 360/390/768/1280/1440 px todos los módulos implementados.
- [ ] EP19-03 Navegación inferior móvil, safe areas, botones táctiles y teclado numérico para importes/cantidades.
- [ ] EP19-04 Formularios compactos, bottom sheets y acciones «registrar» accesibles con una mano.
- [ ] EP19-05 Estados de conexión, carga, error, reintento y sesión caducada.
- [ ] EP19-06 Caching restringido a shell/activos públicos; evitar cachear respuestas personales sensibles de forma insegura.
- [ ] EP19-07 Definir la política de reenvío de formularios y prevenir registros duplicados tras reconexión.
- [ ] EP19-08 Preparar backlog separado, no obligatorio MVP, para soporte offline y sincronización con resolución de conflictos.

**Criterios:** instalación PWA disponible en navegadores compatibles; acciones críticas usables en móvil; no se promete guardar sin internet; ningún importe privado se muestra por caché de sesión ajena.

**Entregables:** PWA instalable y comportamiento responsive probado.

---

### EP-20 — Calidad, seguridad, despliegue y mantenimiento · P0

**Dependencias:** Transversal a todas las épicas · **Estado inicial:** Backlog
**Pantallas:** transversal; K03 y aspectos operativos de K01/K04/K05.

**Objetivo:** validar la calidad integral antes de uso familiar real y asegurar continuidad de datos.

**Tareas:**
- [ ] EP20-01 PHPUnit/Pest: unitarias de cálculos, servicios e integridad; feature tests de Policies y rutas.
- [ ] EP20-02 Casos de integración: presupuesto–gasto; receta–menú–compra–inventario; compra–gasto; fondos–transferencias; plan–compra.
- [ ] EP20-03 Pruebas de autorización para cuentas privadas, consultas de hogar, archivos adjuntos, IA, exportaciones y notificaciones.
- [ ] EP20-04 Pruebas de idempotencia y concurrencia: doble confirmación, dos usuarios editando stock/lista, reintentos de jobs.
- [ ] EP20-05 Seguridad web: XSS, CSRF, validación, subida de archivos, rate limiting, secretos, cifrado donde corresponda.
- [ ] EP20-06 Pruebas UI: flujos end-to-end de PC y mobile, navegación por teclado, contraste y mensajes.
- [ ] EP20-07 Preparar staging, configuración de producción, HTTPS, logs, monitorización, políticas de retención y backups.
- [ ] EP20-08 Ensayo de restauración de backups y despliegue con migraciones seguras.
- [ ] EP20-09 Documentar manual de usuario, guía de operación y procedimiento de soporte.
- [ ] EP20-10 Hacer prueba de aceptación real con ambos usuarios en los cinco recorridos críticos.

**Criterios:** ninguna ruta permite saltarse permisos; pruebas críticas en verde; se pueden restaurar datos; errores no exponen secretos; flujos clave funcionan en PC y móvil; todas las épicas cerradas cumplen definición de terminado.

**Entregables:** versión desplegable, manual y evidencias de pruebas.

---

## 4. Matriz de pantallas (69 principales)

| Grupo | IDs | Tema | Épica responsable |
|---|---|---|---|
| A | A01–A05 | Acceso, invitaciones y onboarding | EP-03 |
| B | B01–B03 | Dashboard, acciones rápidas y notificaciones | EP-16, EP-18 |
| C | C01–C13 | Finanzas, presupuestos, cuentas, deudas, reportes | EP-04, EP-05, EP-06, EP-07, EP-16 |
| D | D01–D07 | Recetas, menú semanal, plantillas y preparación | EP-09, EP-11 |
| E | E01–E07 | Despensa, productos, conteos e historial | EP-10 |
| F | F01–F08 | Tipos de compras, generación, supermercado, confirmación | EP-12, EP-13 |
| G | G01–G05 | Planes, eventos, tareas y calendario | EP-14 |
| H | H01–H05 | Necesidades y metas de ahorro | EP-15, EP-07 |
| I | I01–I06 | Asistente IA y análisis contextuales | EP-17 |
| J | J01–J05 | Integrantes, perfiles, permisos, actividad | EP-03 |
| K | K01–K05 | Configuración, preferencias, importación, PWA | EP-03, EP-08, EP-18, EP-19, EP-20 |

**No confundir 69 pantallas con 69 rutas obligatorias:** algunas serán modales, subpantallas o estados según la arquitectura aprobada. El alcance funcional sí debe quedar cubierto.

## 5. Pruebas críticas obligatorias (E2E)

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

## 6. Decisiones abiertas (pendientes de ADR)

Resolver vía `docs/adr/ADR-XXXX.md` cuando impacten la tarea:

- Cortes fijos configurables y traslado automático presupuestal aprobados; faltan día predeterminado, cambios de corte y tratamiento de déficits o correcciones retroactivas (ADR-0001).
- Cuentas privadas por defecto; el titular puede compartirlas y volverlas privadas sin borrar historial. Faltan detalles de acceso histórico y administración (ADR-0002).
- Pago familiar realizado con cuenta individual.
- Deuda de tarjeta: gasto original vs. liquidación, sin doble cargo.
- Apartados virtuales y reservas reales.
- Unidades: paquete/pieza/kg/L y equivalencias.
- Caducidades por producto o lote.
- Conflictos cuando dos usuarios editan el mismo artículo.
- Proveedor/modelo de OpenRouter, privacidad y retención.
- Regla final de confirmación de consumo y compra (revisar RG-04, RG-06, RG-07).

## 7. Definition of Done

Una tarea **no** está terminada hasta que:

- Cumple la historia, reglas de negocio y pantallas del backlog.
- Valida entradas y permisos desde servidor.
- Maneja errores, estados vacíos/carga/éxito y feedback en interfaz.
- Funciona en desktop y mobile; no rompe 360 px.
- Respeta privacidad e integridad de dinero/stock.
- Incluye pruebas pertinentes y se ejecutaron las que el entorno permite.
- Mantiene datos históricos y migraciones seguras.
- Documenta decisiones, supuestos aprobados y restricciones.
- Informa archivos alterados, verificación realizada y pendientes.

**No marcar una épica como «Terminado» por crear únicamente tablas o vistas.**
