# Fase 4 - Reglas de negocio y automatismos (CI4)

## Implementación aplicada

### 1) Cambio de estado controlado
- Endpoint: `POST /app/ticket/{id}/change-status`
- Método: `RepairApp::changeStatus()`
- Flujo:
  - valida transición permitida por rol (`cr_security_ticket_status_rule`)
  - ejecuta validaciones de negocio por estado
  - actualiza `cr_ticket.cr_status` y campos asociados
  - crea registro append-only en `cr_tickethistory` (from/to/site/user/date/comment)

### 2) Validaciones por estado
Implementadas en `validateStatusBusinessRules()`:
- `Diagnosis`: activo + datos ingreso completos.
- `WaitingParts`: requiere `cr_partrequest` Requested/Ordered o comentario justificado.
- `RepairInProgress`: requiere worklog de inicio o técnico disponible en sede.
- `Testing`: solución obligatoria si sede exige (`cr_requiresolutiontoclose=1`).
- `ReadyToShip`: `TestEvidence` obligatorio si policy (`cr_requiretestingchecklisttoship=1`).
- `Shipped`: requiere movimiento logístico `ShipToCustomer` y `ShippingDoc` si policy.
- `Closed`:
  - `cr_technicalclosureready=1`
  - `cr_administrativeclosuredone=1`
  - si `cr_returnrequired=1`: `cr_returnstatus=Delivered` + movimiento `ReturnIn` hacia `sitein`
  - policies de sede: solución/materiales/checklist según configuración.

### 3) SLA
- Al crear ticket (`ticketCreate`): calcula `cr_sla_duedate` desde `cr_prioritysla`.
- Indicador SLA en dashboard/listado:
  - `in` (en plazo)
  - `risk` (en riesgo <= 24h)
  - `out` (fuera de plazo)

### 4) Transferencia entre sedes
- Endpoint: `POST /app/ticket/{id}/transfer-site`
- Método: `transferSite()`
- Flujo:
  - crea movimiento `TransferOut`
  - actualiza `ticket.cr_currentsite` y `asset.cr_currentsite`
  - marca `ticket.cr_returnrequired=true`, `cr_returnstatus=Planned`
  - agrega entrada en `cr_tickethistory` con comentario

### 5) Retorno obligatorio
- Endpoint `POST /app/ticket/{id}/return-out`: `registerReturnOut()`
  - crea `ReturnOut`
  - set `ticket.cr_returnstatus=InTransit`
- Endpoint `POST /app/ticket/{id}/return-in`: `registerReturnIn()`
  - crea `ReturnIn`
  - set `ticket.cr_returnstatus=Delivered`
  - devuelve ticket/asset a `sitein`
- Bloqueo de cierre final implementado en validación de `Closed`.

### 6) Exchange / sustitución
- Endpoint: `POST /app/ticket/{id}/exchange`
- Método: `createExchangeFromTicket()`
- Flujo:
  - requiere documento de tipo `ExchangeDoc`
  - crea `cr_assetexchange`
  - actualiza activos:
    - incoming -> `Company`, owner null, status `InRepair`
    - replacement -> `Customer`, owner `customerPlant`, status `InService`
  - set `ticket.cr_outcome=ExchangePerformed`
  - registra historia

### 7) No reparable
- Soportado en `changeStatus()` + validación:
  - si `cr_nonrepairable=true` exige motivo (`cr_cancelreason`)
  - aprobación por rol (`cr_Coordinacion`, `cr_Calidad` o `cr_AdminSistema`)

### 8) Evidencias
- Si falla por transporte (texto contiene `transport`): exige `TransportDamagePhoto` para avanzar.
- `TestEvidence` requerido para `ReadyToShip` (policy).

### 9) Append-only
- UI no ofrece edición ni borrado de `cr_tickethistory`.
- Además, triggers/BD de fases previas mantienen la protección.

## Pruebas de escenario

### a) Ticket normal
1. Crear ticket en `/app/ticket-create`.
2. Cambiar estado: `Received -> Diagnosis -> RepairInProgress -> Testing -> ReadyToShip -> Shipped -> Closed`.
3. Verificar:
- entradas en `cr_tickethistory` por cada transición
- validaciones de evidencia en `ReadyToShip/Closed`
- KPI SLA en dashboard.

### b) Transferencia + retorno
1. Abrir ticket y ejecutar `TransferSite` a otra sede.
2. Ejecutar `RegisterReturnOut` y luego `RegisterReturnIn`.
3. Verificar:
- movimientos `TransferOut`, `ReturnOut`, `ReturnIn`
- `cr_returnstatus=Delivered`
- cierre permitido solo tras retorno completo.

### c) Exchange
1. En detalle ticket, completar bloque `CreateExchange` con assets + plant + `ExchangeDoc`.
2. Verificar:
- fila en `cr_assetexchange`
- cambios de ownership en activos
- `ticket.cr_outcome=ExchangePerformed`
- documento `ExchangeDoc` en `cr_ticketdocument`.

### d) Nonrepairable
1. En `ChangeStatus`, marcar `cr_nonrepairable=1` y motivo.
2. Ejecutar con rol autorizado (Coordinación/Calidad/Admin).
3. Verificar:
- bloqueo con rol no autorizado
- aceptación con rol autorizado y motivo.
