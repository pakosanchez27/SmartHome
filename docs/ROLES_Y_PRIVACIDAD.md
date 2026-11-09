# Roles y privacidad — SmartHome

**Épica:** EP-00 · **Tarea:** EP00-05
**Versión:** 1.0 · **Fecha:** 2026-11-05

## 1. Modelo de acceso

Un **usuario** puede pertenecer a uno o más **hogares**. El rol se define por vínculo (`household_user.role`), no por la cuenta. Todos los permisos se validan en servidor con Policies/Gates; ocultar un botón no es autorización.

| Rol | Descripción | Puede |
|---|---|---|
| **Administrador** (`admin`) | Responsable del hogar. Inicialmente ambos adultos. | Todo lo del hogar, invitar/revocar integrantes, cambiar roles, configurar hogar, cerrar periodos, anular operaciones, exportar. |
| **Integrante** (`member`) | Participa en la operación diaria. | Registrar y editar sus movimientos, listas, menús, despensa, planes y necesidades compartidas. No administra integrantes ni configuración global. |
| **Consulta** (`viewer`) | Solo lectura. | Ver recursos compartidos autorizados; no crea, edita, confirma ni anula. |

> Roles previstos en EP-03. Hasta implementarlos, cualquier decisión de permisos se documenta aquí y en ADR-0002.

## 2. Matriz de permisos (resumen)

| Acción | Admin | Integrante | Consulta |
|---|---|---|---|
| Ver dashboard y reportes compartidos | ✅ | ✅ | ✅ |
| Registrar ingreso/gasto/transferencia | ✅ | ✅ | ❌ |
| Anular movimiento | ✅ | Solo propios, si la política lo permite | ❌ |
| Crear/editar presupuesto y cerrar periodo | ✅ | ✅ (borrador/planeado) | ❌ |
| Confirmar compra | ✅ | ✅ | ❌ |
| Ajustar inventario | ✅ | ✅ | ❌ |
| Invitar, revocar, cambiar rol | ✅ | ❌ | ❌ |
| Configurar hogar, moneda, zona horaria, quincenas | ✅ | ❌ | ❌ |
| Exportar datos del hogar | ✅ | ⚠️ según política (ADR) | ❌ |
| Ver actividad de auditoría | ✅ | Parcial (propia) | ❌ |

## 3. Recursos personales vs. compartidos

| Recurso | Personal | Compartido | Regla |
|---|---|---|---|
| Cuentas | Sí (por defecto) | Sí, si se marca explícitamente | Una cuenta privada no aparece en listados, agregados, reportes, notificaciones ni IA de otro usuario. |
| Necesidades/deseos | Sí (por defecto) | Opcional | El propietario decide compartir. |
| Metas y apartados | Sí | Opcional | Ídem. |
| Movimientos | Heredan la visibilidad de la cuenta | — | No se puede "compartir" un movimiento de una cuenta privada. |
| Presupuestos | — | Sí | Pueden filtrarse por ámbito, sin exponer recursos privados. |
| Listas de compras, recetas, menú, despensa, planes | — | Sí | Propios del hogar. |

## 4. Reglas de privacidad no negociables

1. **Aislamiento por hogar:** toda consulta incluye `household_id`; jamás se confía en IDs del cliente.
2. **Aislamiento por titular:** recursos personales exigen `owner_user_id = auth()->id()` además del hogar.
3. **Sin filtración en agregados:** sumar "todas las cuentas" excluye las privadas ajenas.
4. **Sin filtración en búsquedas:** resultados, autocompletado y conteos respetan permisos.
5. **Sin filtración en exportaciones, notificaciones e IA:** las mismas políticas aplican a estos canales.
6. **Invitaciones:** token hasheado, un solo uso, caducidad y revocación; el rol se asigna al aceptar.
7. **Desactivación:** un usuario inactivo no puede autenticarse ni operar, pero su histórico se conserva.
8. **Auditoría:** acciones sensibles registran actor, hogar, entidad, acción, fecha y origen.
9. **Cambio de visibilidad:** solo el titular comparte u oculta sus recursos; la vigencia actual rige para reportes y agregados del hogar — al volver privada una cuenta, sus movimientos dejan de contarse; los totals que omitan cuentas privadas se marcan «No incluye cuentas privadas» (ADR-0002).

## 5. Sesiones y credenciales

- Contraseñas con `bcrypt`/`argon2` (hash de Laravel), nunca en texto plano.
- Sesión por usuario; no se comparten credenciales entre integrantes.
- CSRF en formularios, rate limiting en login y recuperación.
- Recuperación de contraseña con token de un solo uso y caducidad.
- No se registran tokens, contraseñas ni datos financieros completos en logs.

## 6. Pendientes

- Si un integrante puede exportar datos del hogar.
- Si el rol `consulta` puede ver montos o solo totales.
- Procedimiento de transferencia de administración si un adulto queda inactivo.

> La política de cuentas privadas/compartidas quedó resuelta en ADR-0002 (aprobado 2026-11-06);
> ver §4 regla 9.
