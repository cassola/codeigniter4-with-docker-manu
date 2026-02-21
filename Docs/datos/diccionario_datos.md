# Diccionario de Datos (Funcional + Tecnico)

## Convenciones
- Prefijo funcional: `cr_`.
- PK estandar: `id BIGINT UNSIGNED AUTO_INCREMENT`.
- No borrado en tablas criticas por trigger.
- Historial de ticket append-only.

## Tablas de catalogo

| Tabla | Campos principales (tipo) | Indices/relaciones |
|---|---|---|
| `cr_choice_set` | `cr_name VARCHAR(64)`, `cr_description VARCHAR(255)` | `UNIQUE(cr_name)` |
| `cr_choice_option` | `cr_choice_set_id BIGINT`, `cr_value VARCHAR(64)`, `cr_label VARCHAR(128)` | FK a `cr_choice_set` |
| `cr_localizationstring` | `cr_key VARCHAR(120)`, `cr_text_es/pt/it/en VARCHAR(255)`, `cr_module VARCHAR(80)` | `UNIQUE(cr_key)` |
| `cr_prioritysla` | `cr_priority VARCHAR(20)`, `cr_firstresponse_hours INT`, `cr_repairtarget_hours INT`, `cr_closuretarget_hours INT` | `UNIQUE(cr_priority)` |

## Tablas maestras

| Tabla | Campos principales (tipo) | Indices/relaciones |
|---|---|---|
| `cr_site` | `cr_sitecode VARCHAR(8)`, `cr_name VARCHAR(120)`, politicas `cr_requiresolutiontoclose`, `cr_requirematerialstoclose`, `cr_requiretestingchecklisttoship` | `UNIQUE(cr_sitecode)` |
| `cr_accountplant` | `cr_name VARCHAR(160)`, `cr_code VARCHAR(60)` | Referenciada por activo/ticket |
| `cr_person` | `cr_fullname VARCHAR(150)`, `cr_email VARCHAR(191)` | `UNIQUE(cr_email)` |
| `cr_role` | `cr_name VARCHAR(80)` | `UNIQUE(cr_name)` |
| `cr_person_role_site` | `cr_person_id`, `cr_role_id`, `cr_site_id`, `cr_level` | FK a persona/rol/sede |
| `cr_usersettings` | `cr_useremail VARCHAR(191)`, `cr_languagemode`, `cr_preferredlanguage`, `cr_defaultsite_id` | `UNIQUE(cr_useremail)`, FK a `cr_site` |

## Activos y operacion

| Tabla | Campos principales (tipo) | Indices/relaciones |
|---|---|---|
| `cr_asset` | `cr_serialnumber VARCHAR(120)`, `cr_model VARCHAR(120)`, `cr_assetstatus VARCHAR(30)`, `cr_currentsite_id BIGINT` | `idx_cr_serialnumber`, FK a sede y planta |
| `cr_ticket` | `cr_ticketnumber VARCHAR(40)`, `cr_sapnotice VARCHAR(80)`, `cr_status VARCHAR(30)`, `cr_priority VARCHAR(20)`, `cr_sla_duedate DATETIME`, campos de cierre y retorno | `UNIQUE(cr_ticketnumber)`, `idx_cr_sapnotice`, FK a sede/planta/activo |
| `cr_tickethistory` | `cr_ticket_id`, `cr_fromstatus`, `cr_tostatus`, `cr_changedby`, `cr_changedon` | FK a `cr_ticket`; append-only |
| `cr_worklog` | `cr_ticket_id`, `cr_worktype`, `cr_technician_id`, `cr_start`, `cr_end` | FK a ticket, tecnico y sede |
| `cr_ticketmaterial` | `cr_ticket_id`, `cr_partname`, `cr_quantity`, `cr_usedon` | FK a ticket/persona |
| `cr_partrequest` | `cr_ticket_id`, `cr_partname`, `cr_quantity`, `cr_status`, `cr_requestedon` | FK a ticket/persona |
| `cr_movement` | `cr_ticket_id`, `cr_asset_id`, `cr_movementtype`, `cr_fromsite_id`, `cr_tosite_id`, `cr_datetime` | FK a ticket/activo/sede |
| `cr_ticketdocument` | `cr_ticket_id`, `cr_documenttype`, `cr_file`, `cr_author`, `cr_createdon` | FK a ticket |
| `cr_assetexchange` | `cr_ticket_id`, `cr_accountplant_id`, `cr_incomingasset_id`, `cr_replacementasset_id`, `cr_exchangedate` | FK a ticket/planta/activos |
| `cr_nonconformity` | `cr_ticket_id`, `cr_type`, `cr_status`, `cr_detectedon`, `cr_closedon` | FK a ticket/persona |

## Seguridad y gobierno

| Tabla | Campos principales (tipo) | Indices/relaciones |
|---|---|---|
| `cr_security_role` | `cr_name VARCHAR(80)` | Catalogo de roles RBAC |
| `cr_security_table_permission` | `cr_role_id`, `cr_resource`, permisos `cr_can_*`, `cr_scope` | FK a rol |
| `cr_security_user_role` | `cr_useremail`, `cr_role_id`, `cr_isactive` | Usuario -> rol activo |
| `cr_security_ticket_status_rule` | `cr_role_name`, `cr_from_status`, `cr_to_status`, `cr_isactive` | Politica de transiciones |
| `cr_security_user_site_scope` | `cr_useremail`, `cr_site_id`, `cr_isactive` | Scope adicional por sede |
| `cr_team` / `cr_team_member` | asignacion de equipos por sede | alcance operativo multi-sede |

## Relaciones clave
- `cr_ticket` es eje central de trazabilidad.
- Todo evento operativo (historial, worklog, material, parte, movimiento, documento, NC, exchange) referencia ticket.
- `cr_asset` se relaciona con ticket y movimientos para historial fisico.
- `cr_site` gobierna reglas operativas y alcance territorial.

## Indices criticos de busqueda
- `cr_ticket.idx_cr_sapnotice`
- `cr_ticket.uq_cr_ticket_ticketnumber`
- `cr_asset.idx_cr_serialnumber`
- Indices por FK en relaciones operativas.

## Referencia detallada
- Definicion extendida campo por campo: `database/fase1_data_dictionary.md`.
- DDL y seed: `database/fase1_schema_seed.sql`.
