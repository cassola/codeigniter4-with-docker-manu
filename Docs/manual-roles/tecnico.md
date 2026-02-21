# Manual de Rol: Tecnico

## Objetivo del rol
Diagnosticar, reparar, documentar trabajo tecnico y preparar salida a logistica.

## Pantallas usadas
- `app/tickets`
- `app/ticket/{id}`
- `app/parts-requests`
- `app/movements`

## Flujo operativo
1. Tomar ticket en `Diagnosis`.
2. Registrar diagnostico y avance en worklog/materiales.
3. Mover estado: `Diagnosis -> WaitingParts/RepairInProgress`.
4. Ejecutar `RepairInProgress -> Testing`.
5. Cargar evidencias tecnicas (`TestEvidence`, fotos antes/despues, reporte).
6. Pasar a `ReadyToShip` cuando cumple criterios.

## Evidencias tecnicas
- `cr_worklog` (inicio, fin, notas).
- `cr_ticketmaterial` y/o `cr_partrequest`.
- `cr_ticketdocument` (`DiagnosticReport`, `TestEvidence`, `BeforeAfterPhotos`).

## Reglas criticas
- `Testing` y `ReadyToShip` validan evidencia.
- Si `cr_nonrepairable=1`, exige motivo y aprobacion de rol autorizado.
- No cierre administrativo.

## Restricciones
- No borrado en tablas criticas.
- Alcance por sede/relacion multi-sede segun politica de seguridad.
