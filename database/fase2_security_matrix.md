# Fase 2 - Seguridad, alcance multi-sede y gobernanza ISO

## Enfoque implementado
- Modelo RBAC en BD: `cr_security_role`, `cr_security_table_permission`, `cr_security_user_role`.
- Alcance multi-sede recomendado (A): `Teams por sede` con `cr_team` y `cr_team_member`.
- Soporte de alcance adicional por sitio: `cr_security_user_site_scope`.
- Row-level security mediante view `cr_v_ticket_secure` + variables de sesión SQL.
- Reglas ISO: no borrado salvo `cr_AdminSistema`, auditoría (Fase 1), `cr_tickethistory` append-only.

## Roles creados
1. `cr_AdminSistema`
2. `cr_Coordinacion`
3. `cr_Calidad`
4. `cr_Tecnico`
5. `cr_Recepcion`
6. `cr_Logistica`
7. `cr_Administracion`

## Matriz de permisos por tabla (CRUD + gobierno)
Leyenda:
- `C` crear
- `R` leer
- `U` actualizar
- `D` borrar
- `ST` cambiar estado
- `CT` cierre técnico
- `CA` cierre administrativo
- `AP` aprobar (exchange/transfer/NC según contexto)
- `SLA` gestión de SLA
- `Scope`: `OWN_SITE`, `OWN_SITE_PLUS_RELATED`, `ALL_SITES`

| Recurso | AdminSistema | Coordinacion | Calidad | Tecnico | Recepcion | Logistica | Administracion |
|---|---|---|---|---|---|---|---|
| `cr_ticket` | C,R,U,D,ST,CT,CA,AP,SLA / ALL_SITES | C,R,U,ST,CT,AP,SLA / ALL_SITES | R,ST,AP / ALL_SITES | R,ST / OWN_SITE_PLUS_RELATED | C,R,U,ST / OWN_SITE | R,ST / OWN_SITE_PLUS_RELATED | R,U,CA / ALL_SITES |
| `cr_tickethistory` | C,R,U,D / ALL_SITES | C,R / ALL_SITES | C,R / ALL_SITES | C,R / OWN_SITE_PLUS_RELATED | C,R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | C,R / ALL_SITES |
| `cr_worklog` | C,R,U,D / ALL_SITES | C,R,U / ALL_SITES | R / ALL_SITES | C,R,U / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_ticketmaterial` | C,R,U,D / ALL_SITES | C,R,U / ALL_SITES | R / ALL_SITES | C,R,U / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_partrequest` | C,R,U,D / ALL_SITES | C,R,U,AP / ALL_SITES | R / ALL_SITES | C,R,U / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_movement` | C,R,U,D / ALL_SITES | C,R,U,AP / ALL_SITES | R / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | C,R,U / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_ticketdocument` | C,R,U,D / ALL_SITES | C,R,U / ALL_SITES | C,R,U,AP / ALL_SITES | C,R,U / OWN_SITE_PLUS_RELATED | C,R,U / OWN_SITE | C,R,U / OWN_SITE_PLUS_RELATED | C,R,U / ALL_SITES |
| `cr_assetexchange` | C,R,U,D / ALL_SITES | C,R,U,AP / ALL_SITES | R,AP / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_nonconformity` | C,R,U,D / ALL_SITES | C,R,U,AP / ALL_SITES | C,R,U,AP / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_asset` | C,R,U,D / ALL_SITES | C,R,U / ALL_SITES | R / ALL_SITES | R / OWN_SITE_PLUS_RELATED | C,R,U / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_accountplant` | C,R,U,D / ALL_SITES | C,R,U / ALL_SITES | R / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_site` | C,R,U,D / ALL_SITES | R / ALL_SITES | R / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_person` | C,R,U,D / ALL_SITES | C,R,U / ALL_SITES | R / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_usersettings` | C,R,U,D / ALL_SITES | R,U / ALL_SITES | R / ALL_SITES | R,U / OWN_SITE_PLUS_RELATED | R,U / OWN_SITE | R,U / OWN_SITE_PLUS_RELATED | R,U / ALL_SITES |
| `cr_prioritysla` | C,R,U,D / ALL_SITES | R,U,SLA / ALL_SITES | R / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_localizationstring` | C,R,U,D / ALL_SITES | R / ALL_SITES | C,R,U / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_choice_set` | C,R,U,D / ALL_SITES | R / ALL_SITES | C,R,U / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_choice_option` | C,R,U,D / ALL_SITES | R / ALL_SITES | C,R,U / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_person_role_site` | C,R,U,D / ALL_SITES | C,R,U / ALL_SITES | R / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |
| `cr_role` | C,R,U,D / ALL_SITES | R / ALL_SITES | R / ALL_SITES | R / OWN_SITE_PLUS_RELATED | R / OWN_SITE | R / OWN_SITE_PLUS_RELATED | R / ALL_SITES |

## Recomendación concreta de Teams por sede (A)
- Crear 1 team por sede: `TEAM_ESP`, `TEAM_MEX`, `TEAM_BRA`, `TEAM_USA`.
- Asignar cada ticket/activo/transacción a `cr_ownerteam_id`.
- Membresía de usuario en `cr_team_member` para definir alcance operativo base.
- Excepciones de lectura/escritura cruzada por `cr_security_user_site_scope`.
- Usuarios con rol `cr_Coordinacion` y `cr_Calidad` tienen lectura global (sin depender del team).

## Reglas de cambio de estado
- Tabla de políticas: `cr_security_ticket_status_rule`.
- Trigger de seguridad: `trg_cr_ticket_before_update_security`.
- Reglas base:
  - `cr_Recepcion`: `Received -> Diagnosis`.
  - `cr_Tecnico`: `Diagnosis -> WaitingParts/RepairInProgress`, `WaitingParts -> RepairInProgress`, `RepairInProgress -> Testing`, `Testing -> ReadyToShip`.
  - `cr_Logistica`: `ReadyToShip -> Shipped`.
  - `cr_Coordinacion`: `Shipped -> Closed`, y cancelaciones autorizadas.
  - `cr_Administracion`: `Shipped -> Closed` (cierre administrativo).
  - `cr_Calidad`: puede regresar `Testing -> RepairInProgress` por rechazo de calidad.

## Reglas de cierre
- Solo `cr_Coordinacion` o `cr_Administracion` pueden cerrar (`Closed`) en modo no admin.
- Antes de cerrar, obligatorio:
  - `cr_technicalclosureready = 1`
  - `cr_administrativeclosuredone = 1`
  - Evidencia documental mínima en `cr_ticketdocument`: `DiagnosticReport` o `TestEvidence`

## Reglas ISO aplicadas
- No borrado en tablas críticas, salvo `cr_AdminSistema` (`@app_is_admin=1`).
- `cr_tickethistory` append-only (sin update/delete para no-admin).
- Auditoría ya activa (Fase 1) y preservada.

## Variables de sesión requeridas en la app
- `SET @app_useremail = 'usuario@dominio';`
- `SET @app_role_name = 'cr_Tecnico';` (rol activo para transición)
- `SET @app_is_admin = 0|1;`

Estas variables deben setearse por request en un middleware/filtro de CodeIgniter antes de ejecutar DML.
