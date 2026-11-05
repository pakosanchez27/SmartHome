# Decisiones de arquitectura (ADR)

**Épica:** EP-00 · **Tarea:** EP00-06
**Última actualización:** 2026-11-05

## Índice

| ADR | Tema | Estado |
|---|---|---|
| [ADR-0001](./ADR-0001-cortes-quincenales.md) | Cortes de quincena y arrastre de saldo | Propuesto |
| [ADR-0002](./ADR-0002-cuentas-privadas-compartidas.md) | Cuentas privadas/compartidas y administración | Propuesto |
| [ADR-0003](./ADR-0003-pago-familiar-cuenta-individual.md) | Pago familiar con cuenta individual | Propuesto |
| [ADR-0004](./ADR-0004-deuda-tarjeta.md) | Deuda de tarjeta: gasto vs. liquidación | Propuesto |
| [ADR-0005](./ADR-0005-apartados-virtuales.md) | Apartados virtuales y reservas reales | Propuesto |
| [ADR-0006](./ADR-0006-unidades-equivalencias.md) | Unidades, paquetes y equivalencias | Propuesto |
| [ADR-0007](./ADR-0007-caducidades.md) | Caducidad por producto o por lote | Propuesto |
| [ADR-0008](./ADR-0008-edicion-concurrente.md) | Edición concurrente de listas e inventario | Propuesto |
| [ADR-0009](./ADR-0009-openrouter-privacidad.md) | OpenRouter: modelo, privacidad y retención | Propuesto |
| [ADR-0010](./ADR-0010-confirmacion-consumo-compra.md) | Confirmación de consumo y de compra | Propuesto |

> **Estado `Propuesto`:** documenta opciones y recomendación, pero **no** autoriza implementar una regla definitiva. Requiere aprobación del responsable del producto antes de cerrar la tarea que dependa de ella.

## Plantilla

```markdown
# ADR-XXXX — Título

- **Estado:** Propuesto | Aceptado | Reemplazado por ADR-YYYY
- **Fecha:** YYYY-MM-DD
- **Épicas afectadas:** EP-XX
- **Decisor:** (nombre)

## Contexto
Hechos, restricciones y por qué se necesita decidir.

## Opciones
1. Opción A — ventajas / desventajas.
2. Opción B — ventajas / desventajas.

## Recomendación
Opción sugerida y justificación breve.

## Decisión
Pendiente de aprobación / texto de la decisión aprobada y fecha.

## Consecuencias
Impacto en datos, UI, pruebas y migraciones.
```

## Reglas

1. Una ADR por decisión; no se edita una ADR aceptada para cambiar su significado (se reemplaza).
2. Toda ADR aprobada actualiza el backlog y la matriz de trazabilidad.
3. Las decisiones que afecten dinero, privacidad o inventario no se implementan mientras estén en `Propuesto`.
