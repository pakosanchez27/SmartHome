# ADR-0003 — Pago familiar con cuenta individual

- **Estado:** Aprobado
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-04, EP-05, EP-06, EP-16
- **Decisor:** Titular del producto (2026-11-10)

## Contexto

Un gasto del hogar puede pagarse desde la cuenta personal de uno de los integrantes. El sistema no debe inventar reembolsos automáticos, pero debe reflejar la realidad sin duplicar el egreso ni alterar la privacidad de la cuenta.

## Opciones

1. **Gasto en la cuenta real + marca de "reembolsable"** con un registro de deuda interna entre integrantes. Trazable; requiere módulo de cuentas entre personas.
2. **Gasto en la cuenta real sin reembolso**, solo con nota y vínculo al hogar. Simple; no refleja quién debe a quién.
3. **Reembolso automático como transferencia** al confirmar el gasto. Cómodo; puede crear movimientos que no ocurrieron.

## Recomendación

Opción 1 solo si se aprueba un registro explícito de deuda interna; por defecto, opción 2 (gasto real + visibilidad compartida del gasto, sin mover dinero). Nunca generar transferencias automáticas (RG-03).

## Decisión

Se aprueba la **opción 2**: el gasto pertenece a la cuenta real del titular,
se marca como gasto del hogar y no genera reembolso, deuda interna ni
transferencia automática.

## Consecuencias

- El movimiento pertenece a la cuenta utilizada; su ámbito se marca como gasto del hogar.
- Si se implementa deuda interna, requiere entidad y liquidación propia, sin doble egreso.
- Pruebas: gasto familiar desde cuenta privada sin filtrar el saldo al otro usuario.
