# ADR-0001 — Cortes de quincena y arrastre de saldo

- **Estado:** Parcialmente aprobado
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-05, EP-08, EP-13, EP-16
- **Decisor:** Titular del producto; decisiones restantes pendientes

## Contexto

El hogar usa presupuestos quincenales, pero el corte puede variar (día 15/último día, día de pago, semana). El CSV heredado usa periodos de agosto a diciembre de 2026 con cortes propios. Además, un saldo no asignado puede trasladarse al siguiente periodo.

## Opciones

1. **Corte fijo configurable por hogar** (p. ej. día 1–15 y 16–fin de mes), editable en configuración. Simple, predecible; requiere definir qué pasa si cambia el corte a mitad de año.
2. **Corte por día de pago de cada ingreso**, generando periodos irregulares. Más fiel a la realidad; más complejo para comparar y reportar.
3. **Corte manual por periodo**, donde el administrador crea cada quincena. Máxima flexibilidad; riesgo de huecos y errores.

### Arrastre de saldo
- **A. Sin arrastre:** cada quincena parte de cero; el saldo real vive en las cuentas.
- **B. Arrastre informativo:** se muestra el remanente anterior como referencia, sin sumarlo automáticamente.
- **C. Arrastre automático:** el remanente se convierte en importe disponible del siguiente periodo.

## Recomendación

La recomendación inicial fue corte fijo configurable con arrastre informativo. El titular eligió posteriormente el traslado automático presupuestal; ver la decisión siguiente.

## Decisión

El titular aprobó **cortes fijos configurables por hogar** (opción 1) y el **traslado automático del remanente** al siguiente periodo (opción C). El traslado pertenece a la planificación presupuestal: no crea movimientos financieros ni modifica el saldo real de una cuenta (RG-02 y RG-03).

**Pendiente:** día de corte predeterminado, comportamiento al cambiarlo y tratamiento detallado de déficits, correcciones retroactivas y cierres de periodo. Esas reglas no se deducen de la aprobación del traslado automático.

## Consecuencias

- `budget_periods` necesita configuración de corte por hogar y estado.
- La UI debe identificar el remanente trasladado como presupuesto, no como saldo o efectivo disponible real.
- Pruebas: periodos irregulares, cambio de corte y cierre de quincena.
