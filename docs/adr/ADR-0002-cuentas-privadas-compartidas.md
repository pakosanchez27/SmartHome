# ADR-0002 — Cuentas privadas/compartidas y administración

- **Estado:** Aprobado
- **Fecha:** 2026-11-05 (aprobación completa 2026-11-06)
- **Épicas afectadas:** EP-03, EP-04, EP-05, EP-16, EP-17, EP-18
- **Decisor:** Titular del producto

## Contexto

Cada adulto puede tener cuentas personales (efectivo, banco) además de cuentas del hogar. RG-01 exige que una cuenta privada no aparezca en listados, agregados, reportes, notificaciones ni IA de otro usuario. Las alternativas eran: privada por defecto, compartida por defecto u obligatoriamente compartida en presupuestos.

## Decisión

**Las cuentas son privadas por defecto.** Compartir es una acción explícita del titular, con confirmación; el historial se conserva al volver a privada.

Precisiones aprobadas el 2026-11-06:

1. **Agregados:** ningún integrante —incluida la administración— ve saldos o totales que contengan cuentas privadas ajenas. Los totals del hogar que omiten cuentas privadas se marcan «No incluye cuentas privadas» (RG-11: no presentar un total como completo cuando hay cuentas excluidas).
2. **Histórico al cambiar visibilidad:** la **vigencia actual rige**. Al volver privada una cuenta, sus movimientos dejan de contarse en reportes y agregados del hogar desde ese momento; el titular conserva su histórico completo en su propio acceso. Un reporte del hogar «histórico» ahora completo, excluye las cuentas que hoy son privadas.
3. **Administración:** la administración del hogar **no** cambia la visibilidad de cuentas ajenas. Solo el titular comparte u oculta los suyas (confirmación + registro en auditoría, EP03-08).
4. **Integrantes desactivados o retirados:** sus cuentas privadas quedan inaccesibles para todos; si el hogar se los reactiva (suspendidos) o los reinvita (retirados), vuelven a ver las suyas. El retiro no otorga a nadie acceso a datos privados ajenos (RG-01).
5. **Movimientos, apartados y deudas ligados:** heredan la visibilidad del recurso al que pertenecen; no se puede «compartir» un movimiento de una cuenta privada.

Esas reglas se implementan con el mecanismo reutilizable `owner_user_id` + `shared_at` (ver «Consecuencias») y aplican también a necesidades/deseos y metas del titular.

## Consecuencias

- **Mecanismo de datos (contrato EP03-07):** los recursos personales llevan `owner_user_id` (null = recurso del hogar) y `shared_at` timestamp nullable (null = privada; fecha = compartida). Cambiar visibilidad gestiona `shared_at`; el histórico del registro nunca se borra (RG-10).
- **Código reutilizable:** enum `App\Enums\Visibility` y trait `App\Models\Concerns\HasOwnerVisibility` (predicado `isVisibleTo` y alcance `scopeVisibleTo($viewer, $household)`); ambos exigen una membresía activa y el alcance recibe el hogar obligatorio. Los recursos del hogar sin titular son visibles para los roles activos de ese hogar; EP-04+ agrega `household_id` por regla general.
- Reportes y dashboard excluyen cuentas privadas ajenas aunque el total quede incompleto; se indica «No incluye cuentas privadas».
- Pruebas de IDOR y agregados por epic efectos; el Referencia del contrato: docs/ROLES_Y_PRIVACIDAD.md §3–§4.
