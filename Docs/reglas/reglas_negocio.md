# Reglas de Negocio

## 1. Transiciones de estado
- Fuente de control: `cr_security_ticket_status_rule` + validaciones de `RepairApp::changeStatus()`.
- Principio: una transicion solo es valida si rol y estado origen/destino estan autorizados.

## 2. Validaciones por estado
- `WaitingParts`: requiere solicitud de repuesto activa o justificacion.
- `RepairInProgress`: requiere base tecnica de ejecucion (worklog/contexto).
- `Testing`: requiere informacion tecnica minima.
- `ReadyToShip`: requiere evidencia de prueba (`TestEvidence`) cuando la politica lo pide.
- `Shipped`: requiere movimiento logistico y documento de envio si aplica.
- `Closed`: requiere cierre tecnico, cierre administrativo y evidencia minima.

## 3. Retorno obligatorio
- Al transferir sede se activa `cr_returnrequired=1` y `cr_returnstatus=Planned`.
- `return-out` cambia a `InTransit`.
- `return-in` cambia a `Delivered` y retorna activo/ticket a sede origen.
- No se permite `Closed` si retorno obligatorio no esta `Delivered`.

## 4. Exchange
- Requiere `ExchangeDoc`.
- Crea registro en `cr_assetexchange`.
- Ajusta ownership/estado de activos involucrados.
- Marca resultado de ticket (`cr_outcome=ExchangePerformed`).

## 5. No reparable
- Si `cr_nonrepairable=1`, requiere motivo (`cr_cancelreason`).
- Solo roles autorizados pueden aprobar el flujo de no reparable.
- Resultado funcional esperado: `cr_outcome=NonRepairable`.

## 6. Cierre tecnico vs administrativo
- Cierre tecnico: controlado por `cr_technicalclosureready`.
- Cierre administrativo: controlado por `cr_administrativeclosuredone`.
- Cierre final exige ambos controles en estado valido.

## 7. No borrado y auditoria
- Tablas criticas protegidas por triggers de seguridad.
- `cr_tickethistory` es append-only (sin update/delete para operacion normal).
- Registro de trazabilidad por cambios de estado, movimientos y documentos.

## 8. Seguridad y alcance
- RBAC por rol y recurso (`cr_security_table_permission`).
- Alcance por sede/equipo (scope operativo).
- Variables de sesion SQL requeridas por request:
  - `@app_useremail`
  - `@app_role_name`
  - `@app_is_admin`
