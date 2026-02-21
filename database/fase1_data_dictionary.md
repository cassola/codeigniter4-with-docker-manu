# Fase 1 - Diccionario de datos (MySQL)

## Convenciones
- Prefijo funcional: `cr_` en tablas y columnas de negocio.
- PK estándar: `id BIGINT UNSIGNED AUTO_INCREMENT`.
- Soft delete/no borrado: `cr_isactive` y triggers `BEFORE DELETE` en tablas críticas.
- Índices clave: `idx_cr_sapnotice`, `idx_cr_serialnumber`, `uq_cr_ticket_ticketnumber`.
- Choices globales: catálogo en `cr_choice_set` + `cr_choice_option`.

## Choices globales
- `cr_ticketstatus`: Received, Diagnosis, WaitingParts, RepairInProgress, Testing, ReadyToShip, Shipped, Closed, Cancelled.
- `cr_priority`: Low, High, Critical.
- `cr_returnstatus`: Planned, InTransit, Delivered.
- `cr_worktype`: Diagnosis, Repair, Testing, Rework.
- `cr_movementtype`: TransferOut, TransferIn, ReturnOut, ReturnIn, ShipToCustomer, ReceiveFromCustomer.
- `cr_ownertype`: Customer, Company.
- `cr_assetstatus`: InService, InRepair, Quarantine, Scrapped, SpareStock.
- `cr_outcome`: RepairedReturned, ExchangePerformed, NonRepairable, Cancelled, Other.
- `cr_languagemode`: Auto, Manual.
- `cr_language`: es, pt, it, en.
- `cr_partrequeststatus`: Requested, Approved, Ordered, Received, Assigned, Cancelled.
- `cr_documenttype`: TransportDamagePhoto, DiagnosticReport, BeforeAfterPhotos, TestEvidence, ShippingDoc, ExchangeDoc, Other.
- `cr_nctype`: Rework, TestFail, DocumentationError, TransportDamage, RepeatedFailure, Other.
- `cr_ncstatus`: Open, Closed.

## Relaciones 1:N y N:N
- `cr_site` 1:N `cr_person_role_site`, `cr_asset`, `cr_ticket`, `cr_movement`, `cr_usersettings`.
- `cr_person` 1:N `cr_person_role_site`, `cr_worklog`, `cr_ticketmaterial`, `cr_partrequest`, `cr_nonconformity`.
- `cr_role` 1:N `cr_person_role_site`.
- `cr_accountplant` 1:N `cr_asset`, `cr_ticket`, `cr_assetexchange`.
- `cr_asset` 1:N `cr_ticket`, `cr_movement`, `cr_assetexchange`.
- `cr_ticket` 1:N `cr_tickethistory`, `cr_worklog`, `cr_ticketmaterial`, `cr_partrequest`, `cr_movement`, `cr_ticketdocument`, `cr_assetexchange`, `cr_nonconformity`.
- N:N entre `cr_person` y `cr_role` por sede mediante `cr_person_role_site`.

## Diccionario por tabla

### `cr_choice_set`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_name | VARCHAR(64) | UNIQUE, NOT NULL |
| cr_description | VARCHAR(255) | NULL |
| created_at | DATETIME | default CURRENT_TIMESTAMP |
| updated_at | DATETIME | auto update |

### `cr_choice_option`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_choice_set_id | BIGINT UNSIGNED | FK -> `cr_choice_set.id`, NOT NULL |
| cr_value | VARCHAR(64) | NOT NULL |
| cr_label | VARCHAR(128) | NOT NULL |
| cr_sort_order | INT | default 0 |
| created_at | DATETIME | default CURRENT_TIMESTAMP |
| updated_at | DATETIME | auto update |

### `cr_site`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_sitecode | VARCHAR(8) | UNIQUE, NOT NULL |
| cr_name | VARCHAR(120) | NOT NULL |
| cr_country | VARCHAR(120) | NOT NULL |
| cr_city | VARCHAR(120) | NOT NULL |
| cr_isactive | TINYINT(1) | default 1 |
| cr_defaultpriority | VARCHAR(20) | CHECK `Low/High/Critical` |
| cr_defaultinitialstatus | VARCHAR(30) | CHECK `cr_ticketstatus` |
| cr_requiresolutiontoclose | TINYINT(1) | default 1 |
| cr_requirematerialstoclose | TINYINT(1) | default 0 |
| cr_requiretestingchecklisttoship | TINYINT(1) | default 1 |

### `cr_accountplant`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_name | VARCHAR(160) | NOT NULL |
| cr_code | VARCHAR(60) | NULL |
| cr_isactive | TINYINT(1) | default 1 |

### `cr_person`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_fullname | VARCHAR(150) | NOT NULL |
| cr_email | VARCHAR(191) | UNIQUE, NOT NULL |
| cr_phone | VARCHAR(40) | NULL |
| cr_isactive | TINYINT(1) | default 1 |

### `cr_role`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_name | VARCHAR(80) | UNIQUE, NOT NULL |

### `cr_person_role_site`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_person_id | BIGINT UNSIGNED | FK -> `cr_person.id` |
| cr_role_id | BIGINT UNSIGNED | FK -> `cr_role.id` |
| cr_site_id | BIGINT UNSIGNED | FK -> `cr_site.id` |
| cr_startdate | DATE | NOT NULL |
| cr_enddate | DATE | NULL |
| cr_isprimary | TINYINT(1) | default 0 |
| cr_level | VARCHAR(30) | CHECK `Operator/Senior/Approver` |

### `cr_prioritysla`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_priority | VARCHAR(20) | UNIQUE, CHECK `Low/High/Critical` |
| cr_firstresponse_hours | INT | NOT NULL |
| cr_repairtarget_hours | INT | NOT NULL |
| cr_closuretarget_hours | INT | NOT NULL |

### `cr_localizationstring`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_key | VARCHAR(120) | UNIQUE, NOT NULL |
| cr_text_es | VARCHAR(255) | NOT NULL |
| cr_text_pt | VARCHAR(255) | NOT NULL |
| cr_text_it | VARCHAR(255) | NOT NULL |
| cr_text_en | VARCHAR(255) | NOT NULL |
| cr_module | VARCHAR(80) | NULL |
| cr_isactive | TINYINT(1) | default 1 |

### `cr_usersettings`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_useremail | VARCHAR(191) | UNIQUE, NOT NULL |
| cr_languagemode | VARCHAR(20) | CHECK `Auto/Manual` |
| cr_preferredlanguage | VARCHAR(5) | CHECK `es/pt/it/en` |
| cr_defaultsite_id | BIGINT UNSIGNED | FK -> `cr_site.id` |
| cr_defaultview | VARCHAR(80) | NULL |
| cr_notificationsenabled | TINYINT(1) | default 1 |

### `cr_asset`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_assetcode | VARCHAR(80) | NULL |
| cr_serialnumber | VARCHAR(120) | INDEX `idx_cr_serialnumber`, NOT NULL |
| cr_model | VARCHAR(120) | NULL |
| cr_ownertype | VARCHAR(20) | CHECK `Customer/Company` |
| cr_owneraccountplant_id | BIGINT UNSIGNED | FK -> `cr_accountplant.id` |
| cr_assetstatus | VARCHAR(30) | CHECK `cr_assetstatus` |
| cr_currentsite_id | BIGINT UNSIGNED | FK -> `cr_site.id` |
| cr_notes | TEXT | NULL |
| cr_isactive | TINYINT(1) | default 1 |

### `cr_ticket`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticketnumber | VARCHAR(40) | UNIQUE, INDEX |
| cr_sitein_id | BIGINT UNSIGNED | FK -> `cr_site.id`, inmutable por trigger |
| cr_currentsite_id | BIGINT UNSIGNED | FK -> `cr_site.id` |
| cr_closingsite_id | BIGINT UNSIGNED | FK -> `cr_site.id`, NULL |
| cr_sapnotice | VARCHAR(80) | INDEX `idx_cr_sapnotice`, NOT NULL |
| cr_accountplant_id | BIGINT UNSIGNED | FK -> `cr_accountplant.id` |
| cr_asset_id | BIGINT UNSIGNED | FK -> `cr_asset.id` |
| cr_reportedfailure | TEXT | NULL |
| cr_detectedfailure | TEXT | NULL |
| cr_repairsolution | TEXT | NULL |
| cr_priority | VARCHAR(20) | CHECK `Low/High/Critical` |
| cr_status | VARCHAR(30) | CHECK `cr_ticketstatus` |
| cr_sla_duedate | DATETIME | NULL |
| cr_receiveddate | DATETIME | NOT NULL |
| cr_closeddate | DATETIME | NULL |
| cr_nonrepairable | TINYINT(1) | default 0 |
| cr_warranty | TINYINT(1) | default 0 |
| cr_cancelreason | VARCHAR(255) | NULL |
| cr_technicalclosureready | TINYINT(1) | default 0 |
| cr_technicalclosuredate | DATETIME | NULL |
| cr_technicalclosureby | VARCHAR(191) | NULL |
| cr_administrativeclosuredone | TINYINT(1) | default 0 |
| cr_administrativeclosuredate | DATETIME | NULL |
| cr_administrativeclosureby | VARCHAR(191) | NULL |
| cr_returnrequired | TINYINT(1) | default 0 |
| cr_returnstatus | VARCHAR(20) | CHECK `cr_returnstatus` |
| cr_returntargetdate | DATETIME | NULL |
| cr_outcome | VARCHAR(30) | CHECK `cr_outcome` |
| cr_isactive | TINYINT(1) | default 1 |

### `cr_tickethistory` (append-only)
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticket_id | BIGINT UNSIGNED | FK -> `cr_ticket.id` |
| cr_fromstatus | VARCHAR(30) | CHECK `cr_ticketstatus`, NULL |
| cr_tostatus | VARCHAR(30) | CHECK `cr_ticketstatus`, NOT NULL |
| cr_siteattime_id | BIGINT UNSIGNED | FK -> `cr_site.id`, NULL |
| cr_changedby | VARCHAR(191) | NOT NULL |
| cr_changedon | DATETIME | default CURRENT_TIMESTAMP |
| cr_comment | TEXT | NULL |

### `cr_worklog`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticket_id | BIGINT UNSIGNED | FK -> `cr_ticket.id` |
| cr_worktype | VARCHAR(30) | CHECK `cr_worktype` |
| cr_sitework_id | BIGINT UNSIGNED | FK -> `cr_site.id` |
| cr_technician_id | BIGINT UNSIGNED | FK -> `cr_person.id` |
| cr_start | DATETIME | NOT NULL |
| cr_end | DATETIME | NULL |
| cr_result | VARCHAR(120) | NULL |
| cr_notes | TEXT | NULL |

### `cr_ticketmaterial`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticket_id | BIGINT UNSIGNED | FK -> `cr_ticket.id` |
| cr_partname | VARCHAR(160) | NOT NULL |
| cr_quantity | DECIMAL(12,2) | NOT NULL |
| cr_usedby_id | BIGINT UNSIGNED | FK -> `cr_person.id`, NULL |
| cr_usedon | DATETIME | NOT NULL |
| cr_comment | TEXT | NULL |

### `cr_partrequest`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticket_id | BIGINT UNSIGNED | FK -> `cr_ticket.id` |
| cr_partname | VARCHAR(160) | NOT NULL |
| cr_quantity | DECIMAL(12,2) | NOT NULL |
| cr_status | VARCHAR(20) | CHECK `cr_partrequeststatus` |
| cr_requestedby_id | BIGINT UNSIGNED | FK -> `cr_person.id`, NULL |
| cr_requestedby | VARCHAR(191) | NULL |
| cr_requestedon | DATETIME | NOT NULL |
| cr_receivedon | DATETIME | NULL |
| cr_notes | TEXT | NULL |

### `cr_movement`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticket_id | BIGINT UNSIGNED | FK -> `cr_ticket.id`, NULL |
| cr_asset_id | BIGINT UNSIGNED | FK -> `cr_asset.id`, NOT NULL |
| cr_movementtype | VARCHAR(30) | CHECK `cr_movementtype` |
| cr_fromsite_id | BIGINT UNSIGNED | FK -> `cr_site.id`, NULL |
| cr_tosite_id | BIGINT UNSIGNED | FK -> `cr_site.id`, NULL |
| cr_datetime | DATETIME | NOT NULL |
| cr_reference | VARCHAR(120) | NULL |
| cr_executedby | VARCHAR(191) | NULL |
| cr_notes | TEXT | NULL |

### `cr_ticketdocument`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticket_id | BIGINT UNSIGNED | FK -> `cr_ticket.id` |
| cr_documenttype | VARCHAR(30) | CHECK `cr_documenttype` |
| cr_file | VARCHAR(255) | NOT NULL |
| cr_author | VARCHAR(191) | NULL |
| cr_createdon | DATETIME | default CURRENT_TIMESTAMP |
| cr_notes | TEXT | NULL |

### `cr_assetexchange`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticket_id | BIGINT UNSIGNED | FK -> `cr_ticket.id` |
| cr_accountplant_id | BIGINT UNSIGNED | FK -> `cr_accountplant.id` |
| cr_incomingasset_id | BIGINT UNSIGNED | FK -> `cr_asset.id` |
| cr_replacementasset_id | BIGINT UNSIGNED | FK -> `cr_asset.id` |
| cr_exchangedate | DATETIME | NOT NULL |
| cr_reason | VARCHAR(255) | NULL |
| cr_approvedby | VARCHAR(191) | NULL |
| cr_incomingretained | TINYINT(1) | default 1 |
| cr_replacementdelivered | TINYINT(1) | default 1 |

### `cr_nonconformity`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_ticket_id | BIGINT UNSIGNED | FK -> `cr_ticket.id` |
| cr_type | VARCHAR(30) | CHECK `cr_nctype` |
| cr_severity | VARCHAR(20) | NULL |
| cr_description | TEXT | NOT NULL |
| cr_rootcause | TEXT | NULL |
| cr_correctiveaction | TEXT | NULL |
| cr_owner_id | BIGINT UNSIGNED | FK -> `cr_person.id`, NULL |
| cr_status | VARCHAR(20) | CHECK `Open/Closed`, default `Open` |
| cr_closedon | DATETIME | NULL |

### `cr_auditlog`
| Columna | Tipo | Restricciones |
|---|---|---|
| id | BIGINT UNSIGNED | PK |
| cr_tablename | VARCHAR(80) | NOT NULL |
| cr_recordid | BIGINT UNSIGNED | NOT NULL |
| cr_action | VARCHAR(20) | NOT NULL |
| cr_changedon | DATETIME | default CURRENT_TIMESTAMP |
| cr_changedby | VARCHAR(191) | NULL |
| cr_payload | JSON | NULL |

## Auditoría habilitada
- Triggers `AFTER INSERT/AFTER UPDATE` para: `cr_ticket`, `cr_asset`, `cr_tickethistory`, `cr_worklog`, `cr_movement`, `cr_ticketmaterial`, `cr_assetexchange`, `cr_ticketdocument`, `cr_person`, `cr_usersettings`.
- Persistencia en `cr_auditlog` con snapshot JSON compacto por evento.

## Seed incluido
- 4 sedes: ESP, MEX, BRA, USA.
- 14 sets de choices y todas sus opciones.
- 2 clientes/planta.
- 6 roles operativos.
- 4 personas y asignación persona-rol-sede.
- SLA por prioridad (Low/High/Critical).
- 20 claves mínimas de localización i18n.
- 2 activos (Customer y Company).
- 1 ticket ejemplo con historial, worklog, material y movimiento.
