# Matriz de trazabilidad — SmartHome

**Épica:** EP-00 · **Tarea:** EP00-04
**Versión:** 1.0 · **Fecha:** 2026-11-05

> Cada requisito debe tener épica responsable, pantalla(s), prueba(s) y estado. Esta matriz se actualiza al aprobar cambios de alcance; no es evidencia de implementación.

## 1. Leyenda

- **Pantallas:** IDs del brief UI/UX (A01–K05).
- **E2E:** número de la prueba crítica (ver §5 del backlog).
- **Estado:** `Pendiente`, `En progreso`, `En revisión`, `Terminado`. Al 2026-11-05 todo está **Pendiente** salvo la documentación de EP-00.

## 2. Trazabilidad por épica

| Épica | Requisito / historia principal | Pantallas | E2E | Prueba prevista | Estado |
|---|---|---|---|---|---|
| EP-00 | Reglas, glosario, modelo y decisiones trazables | — | — | Revisión documental | En progreso |
| EP-01 | Base Laravel reproducible y segura | — | — | `php artisan test`, `npm run build` | Terminado (2026-11-10) |
| EP-02 | UI consistente y responsive | Transversal (B01) | — | Revisión 360/390/768/1280/1440 | En progreso |
| EP-03 | Dos cuentas, hogar compartido, permisos | A01–A05, J01–J05, K01/K03 | 1, 9, 11 | Feature de auth, invitaciones y Policies | Terminado (2026-11-10) |
| EP-04 | Saldos reales y movimientos únicos | C01, C04–C08, C13 | 2, 3, 12 | Unit de saldo + Feature de transferencias | En progreso (núcleo 2026-11-10; falta EP04-09 y filtros de usuario/ámbito) |
| EP-05 | Presupuesto quincenal vs. real | C02, C03, C01, C12 | 2 | Unit de cálculos + Feature de periodos | Pendiente |
| EP-06 | Pagos programados y deudas sin duplicar | C09, C10 | 4 | Feature de pago y abonos | Pendiente |
| EP-07 | Apartados virtuales y metas | C11, H04, H05 | 4 | Unit de disponible operativo | Pendiente |
| EP-08 | Importación CSV idempotente | K04 | 10 | Feature de importación con archivo de prueba | Pendiente |
| EP-09 | Catálogo y recetas escalables | D01–D03, D05 | 5 | Unit de escalado y conversiones | Pendiente |
| EP-10 | Inventario trazable y concurrente | E01–E07 | 5, 7 | Feature de movimientos y concurrencia | Pendiente |
| EP-11 | Menú sin consumo prematuro | D04, D06, D07 | 5, 7, 8 | Feature de plan y preparación | Pendiente |
| EP-12 | Listas semanales, mensuales y generales | F01–F05, F07, F08 | 5, 8, 9 | Feature de generación no destructiva | Pendiente |
| EP-13 | Compra confirmada = un egreso + stock | F06 | 6, 12 | Feature transaccional e idempotencia | Pendiente |
| EP-14 | Planes con presupuesto y tareas | G01–G05 | — | Feature de planes y permisos | Pendiente |
| EP-15 | Necesidades sin doble gasto | H01–H03, H04/H05 | — | Feature de conversión a meta/compra | Pendiente |
| EP-16 | Dashboard con datos reales | B01, C01, C12 | 2 | Feature de agregados y permisos | Pendiente |
| EP-17 | IA de solo lectura con confirmación | I01–I06 (B01, C02, D04, F02) | 11 | Feature con cliente simulado + validación de schema | Pendiente |
| EP-18 | Alertas verificables y sin duplicados | B03, K02 | — | Feature de jobs y preferencias | Pendiente |
| EP-19 | PWA honesta y UX móvil | K05 + todas | — | Revisión responsive y manifest | Pendiente |
| EP-20 | Seguridad, pruebas y despliegue | Transversal (K03) | 1–12 | Suite completa + restauración de backup | Pendiente |

## 3. Cobertura de pantallas por épica

| Grupo | Pantallas | Épica(s) |
|---|---|---|
| A | A01–A05 | EP-03 |
| B | B01–B03 | EP-16, EP-18 |
| C | C01–C13 | EP-04, EP-05, EP-06, EP-07, EP-16 |
| D | D01–D07 | EP-09, EP-11 |
| E | E01–E07 | EP-10 |
| F | F01–F08 | EP-12, EP-13 |
| G | G01–G05 | EP-14 |
| H | H01–H05 | EP-15, EP-07 |
| I | I01–I06 | EP-17 |
| J | J01–J05 | EP-03 |
| K | K01–K05 | EP-03, EP-08, EP-18, EP-19, EP-20 |

**Cobertura:** 11/11 grupos y 69 pantallas asignadas al menos a una épica.

## 4. Trazabilidad de reglas

| Regla | Épicas que la garantizan | Pruebas |
|---|---|---|
| RG-01 | EP-03, EP-04, EP-10, EP-17 | Feature de Policies, IDOR, agregados |
| RG-02 | EP-04, EP-16 | Unit de reconstrucción de saldo |
| RG-03 | EP-04, EP-05, EP-07 | Feature de transferencias y fondos |
| RG-04 | EP-12, EP-13, EP-06 | Feature de idempotencia |
| RG-05 | EP-09, EP-10, EP-12 | Unit de conversiones |
| RG-06 | EP-11, EP-13 | Feature de menú/preparación |
| RG-07 | EP-08, EP-12 | Feature de regeneración |
| RG-08 | EP-04, EP-05, EP-16 | Feature de estados y reportes |
| RG-09 | EP-17 | Feature con schema y confirmación |
| RG-10 | EP-04, EP-13, EP-20 | Feature de anulación y auditoría |
| RG-11 | EP-18 | Feature de alertas |
| RG-12 | EP-19 | Revisión de manifest y mensajes |

## 5. Requisitos sin épica asignada

Ninguno detectado al 2026-11-05. Si aparece uno, no se implementa hasta asignarle épica, pantalla, prueba y prioridad.
