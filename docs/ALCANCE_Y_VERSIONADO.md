# Alcance, convenciones y versionado — SmartHome

**Épica:** EP-00 · **Tarea:** EP00-07
**Versión:** 1.0 · **Fecha:** 2026-11-05

## 1. Alcance por prioridad

### MVP familiar (P0)
EP-00 a EP-05 (hito 1) y EP-06 a EP-13, EP-16, EP-19, EP-20 (hito 2). Incluye:

- Dos cuentas con hogar compartido, invitaciones y permisos.
- Cuentas, movimientos, presupuesto quincenal/mensual, deudas, apartados e importación del CSV.
- Recetario, despensa, menú, listas de compras y confirmación de compra con egreso + inventario.
- Dashboard, PWA instalable y pruebas críticas.

### Siguiente entrega (P1)
EP-14 (planes), EP-15 (necesidades), EP-18 (alertas y Scheduler).

### Mejora avanzada (P2)
EP-17 (IA con OpenRouter) con confirmación explícita.

### Fuera de alcance del MVP
- App nativa Android/iOS.
- Funcionamiento offline completo y sincronización con resolución de conflictos.
- Integraciones bancarias o precios de tiendas en tiempo real.
- Multitenancy empresarial o SaaS público de hogares.
- IA con permiso de escritura autónoma.
- Pagos o transferencias reales desde la app.

## 2. Convenciones de nomenclatura

### Códigos
| Elemento | Formato | Ejemplo |
|---|---|---|
| Épica | `EP-XX` | `EP-04` |
| Tarea | `EPXX-YY` | `EP04-03` |
| Pantalla | `LetraNN` | `C04` |
| Regla | `RG-XX` | `RG-06` |
| Decisión | `ADR-XXXX` | `ADR-0001` |
| Prueba E2E | `E2E-XX` | `E2E-06` |

### Código
- Clases PHP en `StudlyCase`; tablas y columnas en `snake_case` plural.
- Rutas en español y `kebab-case` (`/presupuestos/quincena-actual`).
- Componentes Blade en `kebab-case` con prefijo por dominio (`<x-finance.money-input>`).
- Variables de entorno en `UPPER_SNAKE_CASE`; secretos solo en `.env`.
- Commits en español, imperativo y con prefijo: `feat:`, `fix:`, `docs:`, `test:`, `chore:`.

## 3. Ramas y versionado

- Rama principal: `main` (protegida una vez exista repositorio remoto).
- Ramas de trabajo: `ep-XX-descripcion-corta`.
- Una tarea no se mezcla sin pruebas y revisión de su DoD.
- Versionado semántico de la aplicación: `MAYOR.MENOR.PARCHE`.
  - `0.x` durante el MVP.
  - `1.0.0` al cerrar el hito 2 y pasar las pruebas de EP-20.

## 4. Estados de una tarea

`Backlog` → `En progreso` → `En revisión` → `Terminado` (o `Bloqueado` con motivo).

Reglas:
- Solo se mueve a **En progreso** cuando existe trabajo real iniciado.
- **Terminado** exige cumplir el DoD, no solo crear tablas o vistas.
- Un bloqueo se documenta con la dependencia o decisión pendiente.

## 5. Gestión de cambios de alcance

1. Se propone el cambio con motivo, impacto en dinero/privacidad/inventario y épicas afectadas.
2. Si hay ambigüedad relevante, se crea un ADR y se solicita decisión.
3. Se actualizan backlog, matriz de trazabilidad y criterios de aceptación.
4. Se registra la fecha y el motivo; no se reescribe el pasado como si ya estuviera implementado.

## 6. Hitos

| Hito | Alcance | Criterio de cierre |
|---|---|---|
| Hito 1 | EP-00 a EP-05 | Dos usuarios autenticados, finanzas y quincenas funcionales. |
| Hito 2 | EP-06 a EP-13, EP-16, EP-19/EP-20 | Menú → compras → inventario y gasto único; MVP familiar. |
| Hito 3 | EP-14, EP-15, EP-18 | Planes, necesidades y notificaciones. |
| Hito 4 | EP-17 | IA contextual con confirmación. |
