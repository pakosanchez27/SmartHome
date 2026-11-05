# Glosario de dominio — SmartHome

**Épica:** EP-00 · **Tarea:** EP00-01
**Versión:** 1.0 · **Fecha:** 2026-11-05

> Un término solo puede significar una cosa en todo el sistema. Si un concepto no está aquí, no se implementa: primero se agrega y se aprueba.

## 1. Organización y acceso

| Término | Definición | No es |
|---|---|---|
| **Hogar** (`household`) | Unidad máxima de pertenencia y compartición de datos. Agrupa a los integrantes, cuentas compartidas, presupuestos, despensa, recetas y planes. | Una cuenta de usuario ni una persona. |
| **Usuario** (`user`) | Persona con credenciales propias, correo y sesión independiente. | Un integrante del hogar: un usuario puede pertenecer a más de un hogar. |
| **Integrante** (`household_user`) | Vínculo entre un usuario y un hogar, con un rol (`administrador`, `integrante`, `consulta`). | El usuario en sí. |
| **Invitación** (`household_invitation`) | Token de un solo uso, con caducidad, que permite a un correo unirse a un hogar. | Un usuario ya creado ni una contraseña temporal. |
| **Rol** | Nivel de permisos dentro de un hogar. | Un permiso individual ni un atributo global de la cuenta. |
| **Ámbito** | Indica si un recurso es **personal** (de un usuario) o **compartido** (de todo el hogar). | Visibilidad de la pantalla ni del navegador. |
| **Titular** (`owner_user_id`) | Usuario dueño de un recurso personal. | Quien lo creó (`created_by`) si son distintos. |
| **Autor** (`created_by`, `updated_by`) | Usuario que creó o modificó un registro, para auditoría. | El titular del recurso. |

## 2. Finanzas

| Término | Definición | No es |
|---|---|---|
| **Cuenta** (`account`) | Contenedor de dinero con tipo (efectivo, banco, débito, crédito, ahorro, otra), saldo inicial y movimientos. Puede ser personal o compartida. | Una tarjeta, un presupuesto ni una categoría. |
| **Saldo real** | Resultado de saldo inicial + movimientos **confirmados** y ajustes trazables. | El saldo del presupuesto ni el dinero "disponible tras compromisos". |
| **Disponible operativo** | Saldo real menos apartados virtuales y compromisos ya reservados. Se calcula, no se almacena como saldo. | El saldo bancario. |
| **Movimiento** (`transaction`) | Hecho financiero confirmado o programado: ingreso, egreso o transferencia. | Una línea de presupuesto. |
| **Ingreso** | Entrada de dinero a una cuenta del hogar. | Un traspaso entre cuentas propias. |
| **Egreso / gasto** | Salida de dinero de una cuenta por una compra, servicio, deuda o consumo. | Una asignación presupuestal ni un apartado. |
| **Transferencia** | Traslado de dinero entre dos cuentas propias. No altera el patrimonio neto del hogar. | Ingreso + egreso. |
| **Ajuste** | Corrección trazable del saldo o de un movimiento, con motivo y actor. | Edición silenciosa del histórico. |
| **Anulación / reverso** | Movimiento compensatorio que deja el original visible y auditable. | Borrado del registro. |
| **Categoría** (`category`) | Clasificación de ingresos/gastos (alimentación, renta, luz, internet, transporte, salud…). | Un presupuesto ni una cuenta. |
| **Presupuesto** (`budget`) | Plan de importes **previstos** para un periodo, por categoría y concepto. | Dinero real ni movimientos. |
| **Periodo presupuestal** (`budget_period`) | Quincena o mes con fechas de corte, estado (borrador, planeado, activo, cerrado) y totales. | Un mes calendario fijo. |
| **Previsto** | Importe planeado en el presupuesto. | Pagado. |
| **Registrado** | Movimiento ya capturado en el libro financiero. | Confirmado/pagado si está programado. |
| **Pagado / confirmado** | Movimiento que ya afectó el saldo de la cuenta. | Previsto ni registrado pendiente. |
| **Deuda** (`debt`) | Obligación con acreedor, saldo, vencimientos e historial de abonos. | Un gasto recurrente ni una tarjeta de crédito en sí. |
| **Pago programado** (`recurring_payment`) | Obligación recurrente con importe, frecuencia y próxima fecha; genera ocurrencias que **no** son egresos hasta confirmarse. | Un movimiento confirmado. |
| **Apartado / fondo** (`fund`) | Reserva **virtual** de dinero disponible para un fin, con objetivo y aportaciones. | Un egreso ni una cuenta bancaria. |
| **Meta de ahorro** (`savings_goal`) | Objetivo con importe, fecha y progreso, alimentado por aportaciones. | Un apartado: una meta puede agrupar aportaciones. |
| **Abono** | Pago parcial o total aplicado a una deuda. | Un nuevo gasto duplicado. |

## 3. Alimentación

| Término | Definición | No es |
|---|---|---|
| **Producto / ingrediente** (`product`) | Artículo único del catálogo del hogar con nombre, categoría, unidad base y equivalencias. Se comparte entre recetas, despensa y compras. | Una marca ni una presentación comercial. |
| **Unidad base** | Unidad canónica de un producto (g, ml, pieza). Toda cantidad se guarda en ella. | La unidad de compra (paquete, kg, L). |
| **Equivalencia** | Conversión declarada entre una presentación y la unidad base (p. ej. 1 paquete = 500 g). | Una suposición implícita. |
| **Receta** (`recipe`) | Preparación reutilizable con porciones base, ingredientes, instrucciones, tiempo y etiquetas. | Un menú planeado. |
| **Ingrediente de receta** (`recipe_ingredient`) | Relación receta–producto con cantidad, unidad y notas. | Una existencia en despensa. |
| **Porción** | Ración unitaria de una receta; escalar porciones multiplica cantidades. | Una pieza indivisible. |
| **Menú semanal** (`meal_plan`) | Plan de 7 días con desayuno, comida, cena y colaciones opcionales. | El recetario. |
| **Comida planeada** (`planned_meal`) | Fila del menú: día, tipo de comida, receta y porciones. | Un consumo confirmado. |
| **Preparación confirmada** | Acción que descuenta inventario según lo realmente usado. | Planear la comida. |
| **Despensa / inventario** (`inventory`) | Existencias reales o estimadas por producto, con mínimos, ubicación y caducidad opcional. | La lista de compras. |

## 4. Compras

| Término | Definición | No es |
|---|---|---|
| **Lista de compras** (`shopping_list`) | Conjunto editable de artículos con cantidades, precios estimados/reales y estado. Tipos: **semanal**, **reposición mensual**, **general**. | Un gasto ni un presupuesto. |
| **Artículo de lista** (`shopping_list_item`) | Línea con producto, cantidad requerida, cantidad comprada, precio estimado/real y estado. | Un movimiento de inventario. |
| **Lista semanal de alimentación** | Faltantes calculados desde el menú y la despensa. | La reposición mensual. |
| **Reposición mensual** | Sugerencia basada en mínimos, consumo e historial; no depende del menú semanal. | Compras generales. |
| **Lista general** | Artículos de casa, uso personal o eventos sin receta. | Comida semanal. |
| **Marcar en carrito** | Indicar que un artículo se tomó en tienda; **no** crea egreso. | Confirmar compra. |
| **Confirmar compra** | Operación transaccional que crea **un** egreso, entradas de inventario y conserva pendientes. | Marcar artículos. |
| **Compra parcial** | Confirmación de solo algunos artículos; el resto queda pendiente. | Cancelación de la lista. |
| **Idempotencia** | Propiedad que evita duplicar una compra/pago ante doble clic o reintento. | Validación de formulario. |

## 5. Planes y necesidades

| Término | Definición | No es |
|---|---|---|
| **Plan / evento** (`plan`) | Acontecimiento con fechas, presupuesto, tareas, compras, responsables y estado (fiesta, viaje, reunión, proyecto). | Un gasto. |
| **Tarea** (`plan_task`) | Actividad con responsable, vencimiento, prioridad y estado dentro de un plan o del hogar. | Un recordatorio de calendario aislado. |
| **Necesidad / deseo** (`need`) | Artículo faltante o deseado con prioridad, precio estimado, fecha objetivo y ámbito. | Una compra ya realizada. |
| **Adquisición** | Necesidad marcada como adquirida **solo** tras confirmar compra o acción explícita auditada. | Editar el estado sin registro. |

## 6. Alertas e IA

| Término | Definición | No es |
|---|---|---|
| **Notificación** | Aviso verificable, con origen en datos reales y preferencias del usuario. | Una recomendación inventada. |
| **Alerta de caducidad** | Aviso basado en fecha/lote capturado. | Estimación sin fecha registrada. |
| **Propuesta de IA** | Sugerencia estructurada y validada por Laravel que requiere confirmación explícita. | Una escritura automática. |
| **Herramienta de IA** | Consulta de solo lectura, acotada por hogar y permisos. | Acceso libre a la base de datos. |

## 7. Términos técnicos

| Término | Definición |
|---|---|
| **PWA** | Aplicación web instalable con manifest y service worker mínimo; requiere conexión para guardar datos en el MVP. |
| **Quincena** | Periodo presupuestal configurable por hogar; no se asume corte fijo (ver ADR-0001). |
| **Hogar activo** | Hogar seleccionado en la sesión del usuario; toda consulta se acota a él. |
| **DoD** | Definition of Done: criterios mínimos para considerar terminada una tarea. |
