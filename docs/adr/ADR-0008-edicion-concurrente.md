# ADR-0008 — Edición concurrente de listas e inventario

- **Estado:** Propuesto
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-10, EP-12, EP-13, EP-20
- **Decisor:** Pendiente (titular del producto)

## Contexto

Dos integrantes pueden editar la misma lista de compras o ajustar el mismo producto al mismo tiempo. El MVP no usa WebSockets; se acepta refresco controlado/AJAX. No deben perderse cambios de forma silenciosa.

## Opciones

1. **Bloqueo optimista con `updated_at`/versión**: al guardar se compara la versión; si cambió, se muestra conflicto y se pide recargar o fusionar.
2. **Bloqueo pesimista**: un usuario "aparta" el registro mientras edita. Evita conflictos; bloquea al otro.
3. **Last-write-wins**: el último guardado gana. Simple; puede borrar trabajo del otro.

## Recomendación

Opción 1 para listas y artículos. En cantidades de inventario, además usar transacción y `lockForUpdate` para evitar stock negativo. Los conflictos se muestran con las diferencias, nunca se sobrescriben en silencio.

## Decisión

Pendiente de aprobación.

## Consecuencias

- `shopping_lists` y `shopping_list_items` con versión/`updated_at` expuesto.
- Respuestas 409 con detalle de diferencias.
- Pruebas de concurrencia con dos usuarios.
