# ADR-0011 — Corrección y anulación de movimientos financieros

- **Estado:** Implementado provisionalmente (pendiente de aprobación del titular)
- **Fecha:** 2026-11-10
- **Épicas afectadas:** EP-04, EP-06, EP-13, EP-20
- **Decisor:** Pendiente (titular del producto)

## Contexto

Un movimiento registrado puede capturarse con un importe o una cuenta equivocada.
RG-02 exige que el saldo sea reproducible desde el histórico y RG-10 exige
trazabilidad de toda operación sensible: nada debe cambiar el dinero sin dejar
rastro. Editar libremente importe o cuenta de una fila ya confirmada reescribiría
la historia y haría imposible explicar el saldo actual.

## Opciones

1. **Edición libre del movimiento** (importe, cuentas incluidas): más cómodo,
   pero reescribe el histórico y complica la auditoría.
2. **Edición de metadatos + anulación y re-registro para el dinero**: la
   edición solo toca concepto, notas, categoría, fecha y visibilidad; cambiar
   importe o cuentas exige anular el movimiento y registrar el correcto.
3. **Contramovimientos automáticos**: cada corrección genera líneas
   compensatorias. Traza completa, pero duplica filas y confunde el historial
   para usuarios no contables.

## Decisión (provisional, implementada en EP-04)

Opción 2. Concretamente:

- **Editar** permite corregir `description`, `notes`, `category_id`,
  `occurred_on` y visibilidad. Los importes y las cuentas no se aceptan del
  formulario (el servidor los ignora si llegan).
- **Anular** marca `status = canceled`, conserva la fila y sus líneas, la
  excluye de saldos y agregados, y registra actor y momento en
  `activity_logs`. No existe ruta de borrado.
- La anulación no es reversible desde la interfaz: para deshacerla se registra
  el movimiento correcto (efecto equivalente al contramovimiento de la opción 3
  sin duplicar líneas).

## Consecuencias

- El saldo siempre se explica: cada cambio de dinero es una alta o una
  anulación auditada.
- El costo de equivocarse en el importe es registrar dos movimientos; la UI
  lo comunica en el detalle y la edición (`finance/transactions/*`).
- Los orígenes externos (compra confirmada, pago de deuda en EP-06/EP-13)
  anularán y re-registrarán siguiendo este mismo contrato vía `origin_*`.
- Aprobación pendiente: si el titular prefiere contramovimientos automáticos,
  `VoidTransaction` es el único punto de cambio.

## Nota de privacidad

La anulación respeta RG-01: un movimiento privado solo puede anularlo su
titular o el hogar con permiso, y el registro de auditoría no expone importes
en texto libre más allá de lo que el actor ya puede ver.
