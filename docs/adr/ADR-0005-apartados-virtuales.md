# ADR-0005 — Apartados virtuales y reservas reales

- **Estado:** Propuesto
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-07, EP-05, EP-16
- **Decisor:** Pendiente (titular del producto)

## Contexto

"APARTADO PARA RENTA" en el CSV no es un gasto. Se necesita reservar dinero para compromisos futuros sin moverlo de la cuenta ni duplicar movimientos. Algunos apartados podrían corresponder a una cuenta de ahorro real.

## Opciones

1. **Solo virtual:** el apartado reduce el disponible operativo calculado, no el saldo contable. Sin movimientos bancarios.
2. **Solo real:** cada apartado exige una transferencia a una cuenta de ahorro dedicada. Contabilidad simple; mucha fricción y afecta el saldo real.
3. **Mixto:** el fondo declara si es virtual o está respaldado por una cuenta real.

## Recomendación

Mixto con **virtual por defecto**. Aportar/retirar de un fondo virtual no crea ingreso ni egreso; si el fondo se marca como real, la aportación es una transferencia y el retiro también. Las metas usan el mismo mecanismo.

## Decisión

Pendiente de aprobación.

## Consecuencias

- `funds` requiere `backing_type` (`virtual` | `account`) y `account_id` opcional.
- `fund_contributions` referencia un movimiento solo cuando el fondo es real.
- Pruebas: aportar no cambia saldo bancario; pagar sí genera un único egreso.
