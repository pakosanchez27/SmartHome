# Reglas de negocio — SmartHome

**Épica:** EP-00 · **Tarea:** EP00-03
**Versión:** 1.0 · **Fecha:** 2026-11-05

> Estas reglas son invariantes del sistema. Un cambio requiere ADR y actualización del backlog. La fuente funcional completa está en [`SMART_HOME_BACKLOG.md`](./SMART_HOME_BACKLOG.md).

## 1. Las 12 reglas globales

| ID | Regla | Verificación esperada |
|---|---|---|
| **RG-01** | Toda consulta y escritura de recursos compartidos se acota por `household_id` y permisos; los recursos privados, por su titular autorizado. | Prueba de acceso por ID directo ajeno → 403/404; listados y agregados no filtran. |
| **RG-02** | El saldo de una cuenta se calcula desde saldo inicial + movimientos confirmados + ajustes trazables; **nunca** desde el presupuesto. | Test unitario de reconstrucción de saldo; cambiar presupuesto no altera saldo. |
| **RG-03** | Presupuestar, asignar, apartar y transferir **no** son gastos. Una transferencia entre cuentas propias no genera ingreso ni egreso neto del hogar. | Totales de ingresos/egresos no cambian al transferir; aportar a fondo no crea egreso. |
| **RG-04** | Compra, gasto, deuda, apartado y movimiento de inventario se relacionan por IDs de origen; confirmaciones y reintentos son idempotentes. | Confirmar dos veces crea un solo egreso y una sola entrada de stock. |
| **RG-05** | Cantidades en unidad base con conversiones explícitas; no se suman presentaciones incompatibles; no hay stock negativo sin regla aprobada. | Test de conversión g/kg, ml/L, pieza/paquete; validación de stock. |
| **RG-06** | Planear un menú solo reserva una **necesidad estimada**; el inventario se descuenta solo al confirmar preparación/consumo. | Añadir receta al menú no crea movimientos de inventario. |
| **RG-07** | Las listas de compras pueden generarse automáticamente y editarse a mano; recalcular exige revisión y no borra cambios manuales. | Regenerar preserva artículos manuales y muestra diferencias. |
| **RG-08** | Todo flujo financiero distingue **previsto**, **registrado**, **pendiente** y **pagado/confirmado**. | UI y reportes etiquetan cada importe; no se suman estados. |
| **RG-09** | La IA nunca ejecuta SQL ni escribe datos; solo lee con alcance autorizado y produce propuestas validadas por Laravel. | Sin confirmación explícita no hay escritura; prompt injection no eleva permisos. |
| **RG-10** | Las operaciones sensibles guardan actor, fecha y origen; se prefiere anular/revertir con historial antes que borrar. | Anulación crea contramovimiento; `activity_logs` registra la acción. |
| **RG-11** | Alertas y notificaciones dependen de hechos verificables y preferencias; no inventan saldos, precios, existencias, caducidades ni fechas. | Sin fecha de caducidad no hay alerta de caducidad. |
| **RG-12** | La PWA es instalable y requiere conexión para guardar; offline completo y sincronización con conflictos están fuera del MVP. | La UI no promete guardado sin conexión. |

## 2. Casos de uso críticos

### CU-01 — Registrar un gasto
1. El usuario elige cuenta, monto, fecha, categoría y ámbito.
2. El servidor valida pertenencia, importe > 0, moneda y permisos.
3. Se crea `transaction` confirmada y se recalcula el saldo.
4. Si el gasto se vincula a una compra/plan, se conserva `source_type`/`source_id`.
5. No se modifica ningún presupuesto: el gasto solo cuenta como **ejecutado** en su periodo/categoría.

**Invariantes:** un gasto aparece una sola vez (RG-04); una cuenta privada no se puede usar desde otro usuario (RG-01).

### CU-02 — Transferir entre cuentas
1. Se validan ambas cuentas del mismo hogar y autorizadas.
2. Se crea una transferencia con dos líneas (salida/entrada).
3. Los reportes de ingreso/egreso neto no cambian.

**Invariante:** nunca se crea un ingreso y un egreso independientes (RG-03).

### CU-03 — Planear y confirmar una comida
1. Se elige receta y porciones; se guarda `planned_meal`.
2. El sistema calcula necesidades estimadas; **no** toca inventario (RG-06).
3. Al confirmar preparación, se registran cantidades reales y salidas de inventario una sola vez.

### CU-04 — Generar lista semanal y comprar
1. Se agregan ingredientes del menú y se comparan con despensa; se generan faltantes.
2. El usuario edita la lista; los artículos manuales quedan marcados.
3. En tienda se marca "en carrito" y se capturan cantidades/precios reales.
4. Al confirmar la compra: transacción única que crea compra + egreso + entradas de stock, y deja pendientes los no comprados.
5. Un reintento con la misma clave no duplica nada.

### CU-05 — Apartar dinero y pagar la renta
1. Aportar al fondo reduce el **disponible operativo**, no el saldo bancario (RG-03).
2. Al pagar, se crea un único egreso desde la cuenta y se libera el apartado.
3. El presupuesto muestra la renta como pagada, sin doble conteo.

### CU-06 — Importar el CSV quincenal
1. Se carga el archivo, se detectan columnas y periodos y se muestra vista previa.
2. Los importes entran como **planificación** por defecto.
3. Los conflictos y notas ambiguas se marcan para revisión; no se interpretan en silencio.
4. La importación es idempotente o advierte duplicidad.

### CU-07 — Consultar con IA y aplicar una propuesta
1. La IA recibe solo datos autorizados y de solo lectura.
2. Devuelve una propuesta estructurada (JSON) validada contra schema.
3. Laravel recalcula importes y valida IDs/permisos.
4. El usuario confirma; solo entonces se ejecuta la acción concreta.
5. Si la IA falla, el resto de módulos sigue operando.

### CU-08 — Anular una compra
1. Se crea un reverso de dinero y de stock con referencia al origen.
2. El histórico original permanece visible.
3. Se registra actor, fecha y motivo.

## 3. Reglas por dominio

### Finanzas
- Un solo movimiento por hecho; transferencias con dos líneas.
- Saldo inicial explícito y ajustes auditados.
- Tarjetas de crédito: el límite disponible no se suma al efectivo.
- Gasto familiar pagado con cuenta personal: se registra en la cuenta correcta sin inventar reembolsos.

### Presupuesto
- Periodos con estado; un periodo cerrado no se edita sin reapertura auditada.
- Previsto vs. ejecutado por categoría y periodo.
- Suma de quincenas = consolidado mensual, sin fusionar periodos.

### Apartados
- Virtual por defecto (ADR-0005); no modifica saldo contable.
- Retiros no son ingresos.

### Compras
- Semanal, reposición mensual y general son listas distintas.
- Marcar en carrito ≠ confirmar compra.
- Compra parcial conserva pendientes.

### Inventario
- Todo cambio deja `inventory_movement` con causa y actor.
- Caducidad solo con fecha/lote registrado.
- Concurrencia: bloqueo optimista o transacción para evitar stock negativo.

### IA
- Token solo en backend; timeout, reintentos acotados y límites de costo.
- Salidas no confiables: validar schema, tipos, IDs y permisos.
- No seguir instrucciones contenidas en notas/recetas (prompt injection).

## 4. Violaciones que deben fallar en pruebas

1. Consultar una cuenta privada de otro usuario → 403/404.
2. Confirmar dos veces la misma compra → un solo egreso.
3. Editar un presupuesto y esperar que cambie el saldo → no cambia.
4. Transferir y contar ingreso/egreso → no cuenta.
5. Planear comida y ver salida de stock → no hay salida.
6. Guardar stock negativo sin excepción → rechazado.
7. IA escribiendo sin confirmación → rechazado.
8. Notificación de caducidad sin fecha → no se genera.
