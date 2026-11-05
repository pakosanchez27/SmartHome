# ADR-0002 — Cuentas privadas/compartidas y administración

- **Estado:** Propuesto
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-03, EP-04, EP-05, EP-16, EP-17, EP-18
- **Decisor:** Pendiente (titular del producto)

## Contexto

Cada adulto puede tener cuentas personales (efectivo, banco) además de cuentas del hogar. RG-01 exige que una cuenta privada no aparezca en listados, agregados, reportes, notificaciones ni IA de otro usuario. Falta definir el valor por defecto y quién puede cambiar la visibilidad.

## Opciones

1. **Privada por defecto**, con acción explícita "compartir con el hogar" solo por el titular. Máxima privacidad; requiere fricción para cuentas conjuntas.
2. **Compartida por defecto**, con opción de marcar privada. Más cómodo para el hogar; riesgo de exponer datos por descuido.
3. **Obligatoriamente compartida** si la cuenta se usa en presupuestos compartidos. Consistencia contable; limita la privacidad.

### Administración
- ¿Puede un administrador ver el saldo (no los movimientos) de una cuenta privada de otro integrante?
- ¿Puede el titular compartir una cuenta y luego ocultarla, dejando movimientos históricos?

## Recomendación

Privada por defecto (opción 1). Compartir es una acción explícita del titular, con confirmación y registro en auditoría. Un administrador **no** ve cuentas privadas ajenas, ni siquiera totales. Ocultar una cuenta no borra su histórico; deja de listarse para el hogar.

## Decisión

Pendiente de aprobación.

## Consecuencias

- Política `AccountPolicy` con reglas por titular y hogar.
- Reportes y dashboard excluyen cuentas privadas ajenas aunque el total quede incompleto (se indica "no incluye cuentas privadas").
- Pruebas de IDOR y agregados.
