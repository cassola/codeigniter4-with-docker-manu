# Manual de Rol: Coordinacion

## Objetivo del rol
Orquestar carga operativa multi-sede, aprobar decisiones y controlar SLA.

## Pantallas usadas
- `app/dashboard`
- `app/tickets`
- `app/ticket/{id}`
- `app/settings/system` (consulta de politicas)

## Flujo operativo
1. Monitorear KPIs: Open, SLA in/risk/out.
2. Gestionar asignaciones y transferencias entre sedes.
3. Autorizar casos de excepcion (exchange, no reparable, cambios de estado especiales).
4. Validar condiciones para cierre tecnico/administrativo.
5. Cerrar ticket cuando cumple requisitos (`Shipped -> Closed`, segun matriz).

## Evidencias de gestion
- KPI y estado actual en dashboard.
- `cr_tickethistory` de decisiones.
- `cr_assetexchange` en sustituciones.
- Campos de cierre en `cr_ticket`.

## Reglas criticas
- Debe respetar politica de transiciones por rol.
- Debe garantizar evidencia documental minima para cierre.
- Debe validar cumplimiento de retorno obligatorio.

## Restricciones
- Sin privilegios de borrado salvo AdminSistema.
