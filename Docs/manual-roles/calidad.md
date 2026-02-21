# Manual de Rol: Calidad

## Objetivo del rol
Asegurar conformidad del proceso, gestionar no conformidades y soporte de auditoria.

## Pantallas usadas
- `app/nonconformities`
- `app/ticket/{id}`
- `app/settings/system`

## Flujo operativo
1. Revisar evidencia tecnica y documental por ticket.
2. Abrir/seguir NC cuando hay hallazgos.
3. Rechazar testing cuando aplica (`Testing -> RepairInProgress`).
4. Supervisar catalogos, checklists y traducciones.
5. Verificar trazabilidad y cumplimiento documental para auditoria.

## Evidencias de calidad
- `cr_nonconformity` (tipo, causa, accion, estado).
- `cr_ticketdocument` y `cr_tickethistory`.
- Politicas en `cr_choice_set`, `cr_choice_option`, `cr_localizationstring`.

## Reglas criticas
- Historial append-only.
- No borrado de registros criticos.
- Evidencia completa para cierre y despacho.

## Restricciones
- Cambios de gobierno segun permisos RBAC.
