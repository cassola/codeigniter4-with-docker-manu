# Matriz ISO 9001: Requisito -> Evidencia en el Sistema

| Requisito ISO 9001 | Evidencia en sistema | Dato verificable |
|---|---|---|
| 4.4 Enfoque por procesos | Flujo de estados del ticket (`Received` a `Closed`) | `cr_ticket.cr_status`, `cr_tickethistory` |
| 5.3 Roles y responsabilidades | RBAC por rol/permiso | `cr_security_role`, `cr_security_table_permission`, `cr_security_user_role` |
| 6.1 Riesgos y oportunidades | Monitoreo de SLA y prioridad | `cr_ticket.cr_priority`, `cr_ticket.cr_sla_duedate`, dashboard KPI |
| 7.1 Recursos operativos | Gestion de activos y sedes | `cr_asset`, `cr_site`, `cr_movement` |
| 7.2 Competencia | Asignacion de personas por rol/sede | `cr_person`, `cr_person_role_site` |
| 7.5 Informacion documentada | Evidencias y anexos por ticket | `cr_ticketdocument.cr_documenttype`, `cr_ticketdocument.cr_file` |
| 8.1 Planificacion y control operacional | Politicas por sede y validaciones de estado | `cr_site` (flags), reglas en `RepairApp::changeStatus()` |
| 8.2 Requisitos del cliente | Registro de aviso SAP y falla reportada | `cr_ticket.cr_sapnotice`, `cr_ticket.cr_reportedfailure` |
| 8.5 Produccion/prestacion del servicio | Worklog, materiales y movimientos | `cr_worklog`, `cr_ticketmaterial`, `cr_movement` |
| 8.6 Liberacion del servicio | Estado `ReadyToShip`/`Shipped` con evidencia | `cr_ticket.cr_status`, `cr_ticketdocument` |
| 8.7 Control de salidas no conformes | Gestion de no conformidades y no reparable | `cr_nonconformity`, `cr_ticket.cr_nonrepairable`, `cr_ticket.cr_cancelreason` |
| 9.1 Seguimiento y medicion | KPIs en dashboard y reporte por estado | `app/dashboard`, `cr_ticket` |
| 9.2 Auditoria interna | Trazabilidad append-only y no borrado | `cr_tickethistory`, triggers de seguridad/auditoria |
| 10.2 No conformidad y accion correctiva | NC abiertas/cerradas y acciones | `cr_nonconformity.cr_status`, fechas de cierre |
| 10.3 Mejora continua | Ajuste de catalogos, SLA y traducciones | `cr_prioritysla`, `cr_choice_*`, `cr_localizationstring` |

## Evidencia adicional de gobierno
- Variables de contexto por request (`@app_useremail`, `@app_role_name`, `@app_is_admin`) para enforcement en trigger.
- Restriccion de borrado para tablas criticas fuera de `cr_AdminSistema`.
- Historial de transiciones sin update/delete operativo.
