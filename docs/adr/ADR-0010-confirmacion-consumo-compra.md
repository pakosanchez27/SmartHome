# ADR-0010 — Confirmación de consumo y de compra

- **Estado:** Propuesto
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-11, EP-12, EP-13, EP-20
- **Decisor:** Pendiente (titular del producto)

## Contexto

Debe quedar claro cuándo se descuenta inventario y cuándo se crea un egreso. RG-04, RG-06 y RG-07 exigen que planear no consuma, que marcar en carrito no gaste y que la confirmación sea única e idempotente.

## Opciones

### Consumo
1. **Descuento al confirmar preparación**, con cantidades reales editables.
2. Descuento al planear el menú (rechazada por RG-06).
3. Descuento manual del usuario en cualquier momento.

### Compra
1. **Un solo flujo de confirmación** que crea compra + egreso + entradas de stock en una transacción.
2. Egreso al marcar cada artículo (rechazada: duplica pagos).
3. Egreso manual posterior a la compra (pierde trazabilidad).

## Recomendación

Consumo: opción 1, con ajuste de cantidades y una única salida por confirmación. Compra: opción 1, con `idempotency_key` única, bloqueo y conservación de pendientes. Los artículos no inventariables generan solo egreso. Devoluciones por reverso trazable.

## Decisión

Pendiente de aprobación.

## Consecuencias

- Servicio transaccional `ConfirmPurchase` y `ConfirmMealPreparation`.
- Pruebas de doble confirmación, compra parcial, reintento y reverso.
- La UI separa claramente "en carrito" de "confirmar compra".
