# ADR-0004 — Deuda de tarjeta: gasto original vs. liquidación

- **Estado:** Aprobado
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-04, EP-06, EP-13
- **Decisor:** Titular del producto (2026-11-10)

## Contexto

Una compra con tarjeta de crédito genera un egreso en el momento de la compra y, al pagar el estado de cuenta, otra salida de dinero desde una cuenta bancaria. Contabilizar ambas como gasto duplica el egreso del hogar.

## Opciones

1. **Gasto al comprar; pago de tarjeta = transferencia** (de banco a tarjeta). Refleja el consumo y evita doble gasto; el saldo de la tarjeta es un pasivo.
2. **Gasto al liquidar**: la compra no afecta finanzas hasta pagar la tarjeta. Refleja el flujo de efectivo; distorsiona el gasto por categoría y periodo.
3. **Modelo mixto configurable por cuenta**. Flexible; difícil de explicar y de probar.

## Recomendación

Opción 1. La compra crea un egreso en la cuenta de crédito (o una línea de pasivo); el pago de la tarjeta es una transferencia que reduce el pasivo y el banco, sin nuevo gasto. El límite disponible no se suma al efectivo.

## Decisión

Se aprueba la **opción 1**: la compra con tarjeta es un gasto al momento de
confirmarla; el pago de la tarjeta es una transferencia desde la cuenta de
origen hacia el pasivo, sin crear un segundo gasto.

## Consecuencias

- `accounts` necesita distinguir activo/pasivo y saldo de tarjeta.
- Reportes separan consumo (gasto) de pago (transferencia).
- Pruebas: compra + pago no duplican egreso; abono parcial actualiza pasivo.
