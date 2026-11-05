# ADR-0001 — Cortes de quincena y arrastre de saldo

- **Estado:** Propuesto
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-05, EP-08, EP-13, EP-16
- **Decisor:** Pendiente (titular del producto)

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

Corte fijo configurable (opción 1) con **arrastre informativo** (B) y posibilidad de asignarlo manualmente. Evita que el presupuesto se comporte como un saldo real (RG-02).

## Decisión

Pendiente de aprobación.

## Consecuencias

- `budget_periods` necesita configuración de corte por hogar y estado.
- La UI debe distinguir "remanente anterior" de "disponible real".
- Pruebas: periodos irregulares, cambio de corte y cierre de quincena.
