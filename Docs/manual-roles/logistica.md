# Manual de Rol: Logistica

## Objetivo del rol
Gestionar movimientos fisicos, envios, retornos y trazabilidad de transporte.

## Pantallas usadas
- `app/movements`
- `app/movements/create`
- `app/ticket/{id}`

## Flujo operativo
1. Recibir ticket en `ReadyToShip`.
2. Registrar movimiento de salida (`ShipToCustomer` o `TransferOut`).
3. Cambiar estado `ReadyToShip -> Shipped` cuando corresponda.
4. Si hay transferencia entre sedes, registrar retorno obligatorio (`ReturnOut`, `ReturnIn`).
5. Completar referencia documental de envio (`ShippingDoc`).

## Evidencias logisticas
- `cr_movement` con tipo, fecha, origen/destino, referencia.
- `cr_ticketdocument` con documentos de despacho/retorno.
- `cr_tickethistory` por cada transicion relevante.

## Reglas criticas
- Cierre final bloqueado si `cr_returnrequired=1` y no esta `Delivered`.
- `Shipped` puede requerir `ShippingDoc` segun politica.

## Restricciones
- No cierra administrativamente.
- No elimina historial ni movimientos criticos.
