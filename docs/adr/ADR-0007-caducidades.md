# ADR-0007 — Caducidad por producto o por lote

- **Estado:** Propuesto
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-10, EP-18
- **Decisor:** Pendiente (titular del producto)

## Contexto

No todos los productos tienen caducidad. Algunos (leche, carnes) se compran en lotes con fechas distintas. Las alertas solo deben existir con fechas verificables (RG-11).

## Opciones

1. **Caducidad a nivel de lote**: cada entrada de inventario puede tener fecha y lote; la alerta usa el lote más próximo.
2. **Caducidad a nivel de producto**: una sola fecha por producto. Simple; imprecisa con compras múltiples.
3. **Sin caducidad en el MVP**: se registra solo si el usuario la captura como nota.

## Recomendación

Opción 1 simplificada: lote opcional por movimiento de entrada; si no hay lote/fecha, no se genera alerta. No se exige capturar caducidad para operar.

## Decisión

Pendiente de aprobación.

## Consecuencias

- `inventory_movements` o `inventory_lots` con `expires_at` nullable y `lot_code` nullable.
- Job de alertas ignora registros sin fecha.
- Pruebas: sin fecha no hay alerta; lote vencido sí.
