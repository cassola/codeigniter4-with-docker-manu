# Procedimiento Operativo Estandar (Ciclo Completo)

## 1. Inicio y recepcion
- Pantalla: `app/ticket-create`
- Accion: alta de ticket con activo, sede, SAP notice, prioridad y falla.
- Resultado: ticket en estado inicial (`Received`) y SLA calculado (`cr_sla_duedate`).

## 2. Diagnostico tecnico
- Pantalla: `app/ticket/{id}`
- Accion: cambio a `Diagnosis`, registro de analisis y evidencia inicial.
- Control: trazabilidad en `cr_tickethistory`.

## 3. Reparacion
- Estados: `WaitingParts` o `RepairInProgress`.
- Soporte: `app/parts-requests`, `app/movements` (si aplica).
- Control: evidencia de materiales/solucion y worklog.

## 4. Testing
- Estado: `Testing`.
- Control: pruebas funcionales y evidencia (`TestEvidence`).
- Salida: si aprueba, avanzar a `ReadyToShip`; si falla, retorna a `RepairInProgress` (calidad/coordinacion segun regla).

## 5. Preparacion de despacho
- Estado: `ReadyToShip`.
- Control: checklist tecnico completo y documentos necesarios.
- Logistica prepara envio.

## 6. Despacho
- Estado: `Shipped`.
- Registros: movimiento (`ShipToCustomer`) y documento de envio (`ShippingDoc`) cuando aplique.

## 7. Retorno entre sedes (si aplica)
- Transferencia: `transfer-site` marca `cr_returnrequired=1`.
- Ejecucion: `return-out` y `return-in`.
- Cierre bloqueado hasta `cr_returnstatus=Delivered`.

## 8. Cierre
- Estado final: `Closed`.
- Requisitos minimos:
  - `cr_technicalclosureready=1`
  - `cr_administrativeclosuredone=1`
  - evidencia documental minima
  - retorno entregado cuando es obligatorio

## 9. Excepciones
- Exchange: crear sustitucion con `ExchangeDoc` y registro en `cr_assetexchange`.
- No reparable: requiere motivo y aprobacion de rol autorizado.
- Cancelacion: solo rutas y roles autorizados por seguridad.

## 10. Evidencia y auditoria
- Historial de estados: `cr_tickethistory` (append-only).
- Movimientos: `cr_movement`.
- Documentos: `cr_ticketdocument`.
- NC: `cr_nonconformity`.
- KPI/SLA: dashboard y listado de tickets.
