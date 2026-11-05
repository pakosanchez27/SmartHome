# ADR-0009 — OpenRouter: modelo, privacidad y retención

- **Estado:** Propuesto
- **Fecha:** 2026-11-05
- **Épicas afectadas:** EP-17, EP-20
- **Decisor:** Pendiente (titular del producto)

## Contexto

La IA debe operar solo desde backend, con datos mínimos y sin capacidad de escritura autónoma (RG-09). Falta elegir modelo, política de retención y qué datos pueden enviarse.

## Opciones

1. **Modelo económico configurable por `.env`** con fallback a "IA no disponible". Bajo costo; calidad variable.
2. **Modelo premium fijo**. Mejor calidad; costo y dependencia.
3. **Sin IA en el MVP**. Cero riesgo; incumple EP-17 (P2, no bloquea MVP).

## Recomendación

Opción 1. `OPENROUTER_MODEL` configurable, timeout corto, límite de tokens/costo por solicitud y por usuario/día. Enviar solo datos agregados necesarios; nunca contraseñas, tokens, notas privadas ajenas ni el CSV completo. Retención de conversaciones configurable (por defecto 30 días) y opción de borrar. Registrar metadatos sanitizados, no prompts con datos sensibles.

## Decisión

Pendiente de aprobación.

## Consecuencias

- `config/services.php` con `openrouter` (key, base_url, model, timeout, max_tokens, daily_budget).
- `AiService`/`OpenRouterClient` con validación de schema, rate limiting y fallback.
- Pruebas con cliente simulado; nunca llamadas reales en tests.
