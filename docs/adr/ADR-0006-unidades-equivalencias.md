# ADR-0006 — Unidades, paquetes y equivalencias

- **Estado:** Propuesto
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-09, EP-10, EP-11, EP-12
- **Decisor:** Pendiente (titular del producto)

## Contexto

Las recetas usan gramos, mililitros y piezas; las compras usan paquetes, kilos y litros. Sumar "1 paquete" con "2 piezas" produce datos falsos (RG-05).

## Opciones

1. **Unidad base obligatoria + equivalencia declarada por producto.** Todo se guarda en g/ml/pieza; las presentaciones tienen factor (1 paquete = 500 g). Robusto; exige capturar equivalencias.
2. **Unidades libres y conversión al vuelo.** Menos captura; riesgo de conversiones imposibles.
3. **Solo unidades de compra.** Simple; impide escalar recetas y comparar despensa.

## Recomendación

Opción 1. Un producto sin equivalencia declarada no puede compararse entre receta y compra; la UI marca "equivalencia pendiente" en lugar de inventar. Los redondeos se definen por unidad (piezas indivisibles suben al entero; g/ml permiten decimales).

## Decisión

Pendiente de aprobación.

## Consecuencias

- `products.unit_base` obligatorio; `product_equivalences` con factor > 0.
- Conversiones validadas en servidor; tests de g↔kg, ml↔L, pieza↔paquete.
- Reportes de faltantes muestran "sin equivalencia" cuando falte el dato.
