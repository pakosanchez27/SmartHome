# ADR-0002 — Cuentas privadas/compartidas y administración

- **Estado:** Parcialmente aprobado
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-03, EP-04, EP-05, EP-16, EP-17, EP-18
- **Decisor:** Titular del producto; decisiones restantes pendientes

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

El titular confirmó que **las cuentas serán privadas por defecto**. RG-01 impide que otros integrantes consulten una cuenta privada, incluidos sus saldos y agregados. El titular puede compartirla con el hogar y después volverla privada; el historial se conserva.

**Pendiente de precisar:** efectos concretos sobre movimientos, reportes y agregados históricos al cambiar la visibilidad, y si otras personas pueden administrarla. Conservar el historial no implica autorizar su consulta a integrantes sin permiso.

## Consecuencias

- Política `AccountPolicy` con reglas por titular y hogar.
- Reportes y dashboard excluyen cuentas privadas ajenas aunque el total quede incompleto (se indica "no incluye cuentas privadas").
- Pruebas de IDOR y agregados.
