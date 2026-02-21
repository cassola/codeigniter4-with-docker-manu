SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

DROP TABLE IF EXISTS cr_auditlog;
DROP TABLE IF EXISTS cr_nonconformity;
DROP TABLE IF EXISTS cr_assetexchange;
DROP TABLE IF EXISTS cr_ticketdocument;
DROP TABLE IF EXISTS cr_movement;
DROP TABLE IF EXISTS cr_partrequest;
DROP TABLE IF EXISTS cr_ticketmaterial;
DROP TABLE IF EXISTS cr_worklog;
DROP TABLE IF EXISTS cr_tickethistory;
DROP TABLE IF EXISTS cr_ticket;
DROP TABLE IF EXISTS cr_asset;
DROP TABLE IF EXISTS cr_usersettings;
DROP TABLE IF EXISTS cr_localizationstring;
DROP TABLE IF EXISTS cr_prioritysla;
DROP TABLE IF EXISTS cr_person_role_site;
DROP TABLE IF EXISTS cr_role;
DROP TABLE IF EXISTS cr_person;
DROP TABLE IF EXISTS cr_accountplant;
DROP TABLE IF EXISTS cr_site;
DROP TABLE IF EXISTS cr_choice_option;
DROP TABLE IF EXISTS cr_choice_set;

SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE cr_choice_set (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_name VARCHAR(64) NOT NULL UNIQUE,
  cr_description VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_choice_option (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_choice_set_id BIGINT UNSIGNED NOT NULL,
  cr_value VARCHAR(64) NOT NULL,
  cr_label VARCHAR(128) NOT NULL,
  cr_sort_order INT NOT NULL DEFAULT 0,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_choice_option_set_value (cr_choice_set_id, cr_value),
  CONSTRAINT fk_cr_choice_option_set FOREIGN KEY (cr_choice_set_id) REFERENCES cr_choice_set(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_site (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_sitecode VARCHAR(8) NOT NULL,
  cr_name VARCHAR(120) NOT NULL,
  cr_country VARCHAR(120) NOT NULL,
  cr_city VARCHAR(120) NOT NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  cr_defaultpriority VARCHAR(20) NOT NULL DEFAULT 'Low',
  cr_defaultinitialstatus VARCHAR(30) NOT NULL DEFAULT 'Received',
  cr_requiresolutiontoclose TINYINT(1) NOT NULL DEFAULT 1,
  cr_requirematerialstoclose TINYINT(1) NOT NULL DEFAULT 0,
  cr_requiretestingchecklisttoship TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_site_sitecode (cr_sitecode),
  CONSTRAINT chk_cr_site_defaultpriority CHECK (cr_defaultpriority IN ('Low', 'High', 'Critical')),
  CONSTRAINT chk_cr_site_defaultstatus CHECK (cr_defaultinitialstatus IN ('Received', 'Diagnosis', 'WaitingParts', 'RepairInProgress', 'Testing', 'ReadyToShip', 'Shipped', 'Closed', 'Cancelled'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_accountplant (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_name VARCHAR(160) NOT NULL,
  cr_code VARCHAR(60) NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_accountplant_name_code (cr_name, cr_code)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_person (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_fullname VARCHAR(150) NOT NULL,
  cr_email VARCHAR(191) NOT NULL,
  cr_phone VARCHAR(40) NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_person_email (cr_email)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_role (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_name VARCHAR(80) NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_role_name (cr_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_person_role_site (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_person_id BIGINT UNSIGNED NOT NULL,
  cr_role_id BIGINT UNSIGNED NOT NULL,
  cr_site_id BIGINT UNSIGNED NOT NULL,
  cr_startdate DATE NOT NULL,
  cr_enddate DATE NULL,
  cr_isprimary TINYINT(1) NOT NULL DEFAULT 0,
  cr_level VARCHAR(30) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_person_role_site_person (cr_person_id),
  KEY idx_cr_person_role_site_role (cr_role_id),
  KEY idx_cr_person_role_site_site (cr_site_id),
  CONSTRAINT fk_cr_person_role_site_person FOREIGN KEY (cr_person_id) REFERENCES cr_person(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_person_role_site_role FOREIGN KEY (cr_role_id) REFERENCES cr_role(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_person_role_site_site FOREIGN KEY (cr_site_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_person_role_site_level CHECK (cr_level IS NULL OR cr_level IN ('Operator', 'Senior', 'Approver'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_prioritysla (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_priority VARCHAR(20) NOT NULL,
  cr_firstresponse_hours INT NOT NULL,
  cr_repairtarget_hours INT NOT NULL,
  cr_closuretarget_hours INT NOT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_prioritysla_priority (cr_priority),
  CONSTRAINT chk_cr_prioritysla_priority CHECK (cr_priority IN ('Low', 'High', 'Critical'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_localizationstring (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_key VARCHAR(120) NOT NULL,
  cr_text_es VARCHAR(255) NOT NULL,
  cr_text_pt VARCHAR(255) NOT NULL,
  cr_text_it VARCHAR(255) NOT NULL,
  cr_text_en VARCHAR(255) NOT NULL,
  cr_module VARCHAR(80) NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_localizationstring_key (cr_key)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_usersettings (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_useremail VARCHAR(191) NOT NULL,
  cr_languagemode VARCHAR(20) NOT NULL DEFAULT 'Auto',
  cr_preferredlanguage VARCHAR(5) NOT NULL DEFAULT 'en',
  cr_defaultsite_id BIGINT UNSIGNED NULL,
  cr_defaultview VARCHAR(80) NULL,
  cr_notificationsenabled TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_usersettings_useremail (cr_useremail),
  KEY idx_cr_usersettings_defaultsite (cr_defaultsite_id),
  CONSTRAINT fk_cr_usersettings_defaultsite FOREIGN KEY (cr_defaultsite_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_usersettings_languagemode CHECK (cr_languagemode IN ('Auto', 'Manual')),
  CONSTRAINT chk_cr_usersettings_preferredlanguage CHECK (cr_preferredlanguage IN ('es', 'pt', 'it', 'en'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_asset (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_assetcode VARCHAR(80) NULL,
  cr_serialnumber VARCHAR(120) NOT NULL,
  cr_model VARCHAR(120) NULL,
  cr_ownertype VARCHAR(20) NOT NULL,
  cr_owneraccountplant_id BIGINT UNSIGNED NULL,
  cr_assetstatus VARCHAR(30) NOT NULL,
  cr_currentsite_id BIGINT UNSIGNED NULL,
  cr_notes TEXT NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_serialnumber (cr_serialnumber),
  KEY idx_cr_asset_owneraccountplant (cr_owneraccountplant_id),
  KEY idx_cr_asset_currentsite (cr_currentsite_id),
  CONSTRAINT fk_cr_asset_owneraccountplant FOREIGN KEY (cr_owneraccountplant_id) REFERENCES cr_accountplant(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_asset_currentsite FOREIGN KEY (cr_currentsite_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_asset_ownertype CHECK (cr_ownertype IN ('Customer', 'Company')),
  CONSTRAINT chk_cr_asset_assetstatus CHECK (cr_assetstatus IN ('InService', 'InRepair', 'Quarantine', 'Scrapped', 'SpareStock'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_ticket (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticketnumber VARCHAR(40) NOT NULL,
  cr_sitein_id BIGINT UNSIGNED NOT NULL,
  cr_currentsite_id BIGINT UNSIGNED NOT NULL,
  cr_closingsite_id BIGINT UNSIGNED NULL,
  cr_sapnotice VARCHAR(80) NOT NULL,
  cr_accountplant_id BIGINT UNSIGNED NOT NULL,
  cr_asset_id BIGINT UNSIGNED NOT NULL,
  cr_reportedfailure TEXT NULL,
  cr_detectedfailure TEXT NULL,
  cr_repairsolution TEXT NULL,
  cr_priority VARCHAR(20) NOT NULL,
  cr_status VARCHAR(30) NOT NULL,
  cr_sla_duedate DATETIME NULL,
  cr_receiveddate DATETIME NOT NULL,
  cr_closeddate DATETIME NULL,
  cr_nonrepairable TINYINT(1) NOT NULL DEFAULT 0,
  cr_warranty TINYINT(1) NOT NULL DEFAULT 0,
  cr_cancelreason VARCHAR(255) NULL,
  cr_technicalclosureready TINYINT(1) NOT NULL DEFAULT 0,
  cr_technicalclosuredate DATETIME NULL,
  cr_technicalclosureby VARCHAR(191) NULL,
  cr_administrativeclosuredone TINYINT(1) NOT NULL DEFAULT 0,
  cr_administrativeclosuredate DATETIME NULL,
  cr_administrativeclosureby VARCHAR(191) NULL,
  cr_returnrequired TINYINT(1) NOT NULL DEFAULT 0,
  cr_returnstatus VARCHAR(20) NULL,
  cr_returntargetdate DATETIME NULL,
  cr_outcome VARCHAR(30) NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_ticket_ticketnumber (cr_ticketnumber),
  KEY idx_cr_sapnotice (cr_sapnotice),
  KEY idx_cr_ticket_sitein (cr_sitein_id),
  KEY idx_cr_ticket_currentsite (cr_currentsite_id),
  KEY idx_cr_ticket_closingsite (cr_closingsite_id),
  KEY idx_cr_ticket_accountplant (cr_accountplant_id),
  KEY idx_cr_ticket_asset (cr_asset_id),
  CONSTRAINT fk_cr_ticket_sitein FOREIGN KEY (cr_sitein_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_ticket_currentsite FOREIGN KEY (cr_currentsite_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_ticket_closingsite FOREIGN KEY (cr_closingsite_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_ticket_accountplant FOREIGN KEY (cr_accountplant_id) REFERENCES cr_accountplant(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_ticket_asset FOREIGN KEY (cr_asset_id) REFERENCES cr_asset(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_ticket_priority CHECK (cr_priority IN ('Low', 'High', 'Critical')),
  CONSTRAINT chk_cr_ticket_status CHECK (cr_status IN ('Received', 'Diagnosis', 'WaitingParts', 'RepairInProgress', 'Testing', 'ReadyToShip', 'Shipped', 'Closed', 'Cancelled')),
  CONSTRAINT chk_cr_ticket_returnstatus CHECK (cr_returnstatus IS NULL OR cr_returnstatus IN ('Planned', 'InTransit', 'Delivered')),
  CONSTRAINT chk_cr_ticket_outcome CHECK (cr_outcome IS NULL OR cr_outcome IN ('RepairedReturned', 'ExchangePerformed', 'NonRepairable', 'Cancelled', 'Other'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_tickethistory (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticket_id BIGINT UNSIGNED NOT NULL,
  cr_fromstatus VARCHAR(30) NULL,
  cr_tostatus VARCHAR(30) NOT NULL,
  cr_siteattime_id BIGINT UNSIGNED NULL,
  cr_changedby VARCHAR(191) NOT NULL,
  cr_changedon DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cr_comment TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_tickethistory_ticket (cr_ticket_id),
  KEY idx_cr_tickethistory_siteattime (cr_siteattime_id),
  CONSTRAINT fk_cr_tickethistory_ticket FOREIGN KEY (cr_ticket_id) REFERENCES cr_ticket(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_tickethistory_siteattime FOREIGN KEY (cr_siteattime_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_tickethistory_fromstatus CHECK (cr_fromstatus IS NULL OR cr_fromstatus IN ('Received', 'Diagnosis', 'WaitingParts', 'RepairInProgress', 'Testing', 'ReadyToShip', 'Shipped', 'Closed', 'Cancelled')),
  CONSTRAINT chk_cr_tickethistory_tostatus CHECK (cr_tostatus IN ('Received', 'Diagnosis', 'WaitingParts', 'RepairInProgress', 'Testing', 'ReadyToShip', 'Shipped', 'Closed', 'Cancelled'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_worklog (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticket_id BIGINT UNSIGNED NOT NULL,
  cr_worktype VARCHAR(30) NOT NULL,
  cr_sitework_id BIGINT UNSIGNED NOT NULL,
  cr_technician_id BIGINT UNSIGNED NOT NULL,
  cr_start DATETIME NOT NULL,
  cr_end DATETIME NULL,
  cr_result VARCHAR(120) NULL,
  cr_notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_worklog_ticket (cr_ticket_id),
  KEY idx_cr_worklog_sitework (cr_sitework_id),
  KEY idx_cr_worklog_technician (cr_technician_id),
  CONSTRAINT fk_cr_worklog_ticket FOREIGN KEY (cr_ticket_id) REFERENCES cr_ticket(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_worklog_sitework FOREIGN KEY (cr_sitework_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_worklog_technician FOREIGN KEY (cr_technician_id) REFERENCES cr_person(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_worklog_worktype CHECK (cr_worktype IN ('Diagnosis', 'Repair', 'Testing', 'Rework'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_ticketmaterial (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticket_id BIGINT UNSIGNED NOT NULL,
  cr_partname VARCHAR(160) NOT NULL,
  cr_quantity DECIMAL(12,2) NOT NULL,
  cr_usedby_id BIGINT UNSIGNED NULL,
  cr_usedon DATETIME NOT NULL,
  cr_comment TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_ticketmaterial_ticket (cr_ticket_id),
  KEY idx_cr_ticketmaterial_usedby (cr_usedby_id),
  CONSTRAINT fk_cr_ticketmaterial_ticket FOREIGN KEY (cr_ticket_id) REFERENCES cr_ticket(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_ticketmaterial_usedby FOREIGN KEY (cr_usedby_id) REFERENCES cr_person(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_partrequest (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticket_id BIGINT UNSIGNED NOT NULL,
  cr_partname VARCHAR(160) NOT NULL,
  cr_quantity DECIMAL(12,2) NOT NULL,
  cr_status VARCHAR(20) NOT NULL,
  cr_requestedby_id BIGINT UNSIGNED NULL,
  cr_requestedby VARCHAR(191) NULL,
  cr_requestedon DATETIME NOT NULL,
  cr_receivedon DATETIME NULL,
  cr_notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_partrequest_ticket (cr_ticket_id),
  KEY idx_cr_partrequest_requestedby_id (cr_requestedby_id),
  CONSTRAINT fk_cr_partrequest_ticket FOREIGN KEY (cr_ticket_id) REFERENCES cr_ticket(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_partrequest_requestedby FOREIGN KEY (cr_requestedby_id) REFERENCES cr_person(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_partrequest_status CHECK (cr_status IN ('Requested', 'Approved', 'Ordered', 'Received', 'Assigned', 'Cancelled'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_movement (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticket_id BIGINT UNSIGNED NULL,
  cr_asset_id BIGINT UNSIGNED NOT NULL,
  cr_movementtype VARCHAR(30) NOT NULL,
  cr_fromsite_id BIGINT UNSIGNED NULL,
  cr_tosite_id BIGINT UNSIGNED NULL,
  cr_datetime DATETIME NOT NULL,
  cr_reference VARCHAR(120) NULL,
  cr_executedby VARCHAR(191) NULL,
  cr_notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_movement_ticket (cr_ticket_id),
  KEY idx_cr_movement_asset (cr_asset_id),
  KEY idx_cr_movement_fromsite (cr_fromsite_id),
  KEY idx_cr_movement_tosite (cr_tosite_id),
  CONSTRAINT fk_cr_movement_ticket FOREIGN KEY (cr_ticket_id) REFERENCES cr_ticket(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_movement_asset FOREIGN KEY (cr_asset_id) REFERENCES cr_asset(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_movement_fromsite FOREIGN KEY (cr_fromsite_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_movement_tosite FOREIGN KEY (cr_tosite_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_movement_type CHECK (cr_movementtype IN ('TransferOut', 'TransferIn', 'ReturnOut', 'ReturnIn', 'ShipToCustomer', 'ReceiveFromCustomer'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_ticketdocument (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticket_id BIGINT UNSIGNED NOT NULL,
  cr_documenttype VARCHAR(30) NOT NULL,
  cr_file VARCHAR(255) NOT NULL,
  cr_author VARCHAR(191) NULL,
  cr_createdon DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cr_notes TEXT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_ticketdocument_ticket (cr_ticket_id),
  CONSTRAINT fk_cr_ticketdocument_ticket FOREIGN KEY (cr_ticket_id) REFERENCES cr_ticket(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_ticketdocument_type CHECK (cr_documenttype IN ('TransportDamagePhoto', 'DiagnosticReport', 'BeforeAfterPhotos', 'TestEvidence', 'ShippingDoc', 'ExchangeDoc', 'Other'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_assetexchange (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticket_id BIGINT UNSIGNED NOT NULL,
  cr_accountplant_id BIGINT UNSIGNED NOT NULL,
  cr_incomingasset_id BIGINT UNSIGNED NOT NULL,
  cr_replacementasset_id BIGINT UNSIGNED NOT NULL,
  cr_exchangedate DATETIME NOT NULL,
  cr_reason VARCHAR(255) NULL,
  cr_approvedby VARCHAR(191) NULL,
  cr_incomingretained TINYINT(1) NOT NULL DEFAULT 1,
  cr_replacementdelivered TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_assetexchange_ticket (cr_ticket_id),
  KEY idx_cr_assetexchange_accountplant (cr_accountplant_id),
  KEY idx_cr_assetexchange_incomingasset (cr_incomingasset_id),
  KEY idx_cr_assetexchange_replacementasset (cr_replacementasset_id),
  CONSTRAINT fk_cr_assetexchange_ticket FOREIGN KEY (cr_ticket_id) REFERENCES cr_ticket(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_assetexchange_accountplant FOREIGN KEY (cr_accountplant_id) REFERENCES cr_accountplant(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_assetexchange_incomingasset FOREIGN KEY (cr_incomingasset_id) REFERENCES cr_asset(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_assetexchange_replacementasset FOREIGN KEY (cr_replacementasset_id) REFERENCES cr_asset(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_nonconformity (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_ticket_id BIGINT UNSIGNED NOT NULL,
  cr_type VARCHAR(30) NOT NULL,
  cr_severity VARCHAR(20) NULL,
  cr_description TEXT NOT NULL,
  cr_rootcause TEXT NULL,
  cr_correctiveaction TEXT NULL,
  cr_owner_id BIGINT UNSIGNED NULL,
  cr_status VARCHAR(20) NOT NULL DEFAULT 'Open',
  cr_closedon DATETIME NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  KEY idx_cr_nonconformity_ticket (cr_ticket_id),
  KEY idx_cr_nonconformity_owner (cr_owner_id),
  CONSTRAINT fk_cr_nonconformity_ticket FOREIGN KEY (cr_ticket_id) REFERENCES cr_ticket(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT fk_cr_nonconformity_owner FOREIGN KEY (cr_owner_id) REFERENCES cr_person(id) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT chk_cr_nonconformity_type CHECK (cr_type IN ('Rework', 'TestFail', 'DocumentationError', 'TransportDamage', 'RepeatedFailure', 'Other')),
  CONSTRAINT chk_cr_nonconformity_status CHECK (cr_status IN ('Open', 'Closed'))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE cr_auditlog (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_tablename VARCHAR(80) NOT NULL,
  cr_recordid BIGINT UNSIGNED NOT NULL,
  cr_action VARCHAR(20) NOT NULL,
  cr_changedon DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  cr_changedby VARCHAR(191) NULL,
  cr_payload JSON NULL,
  KEY idx_cr_auditlog_tablename_recordid (cr_tablename, cr_recordid),
  KEY idx_cr_auditlog_changedon (cr_changedon)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cr_choice_set (cr_name, cr_description) VALUES
('cr_ticketstatus', 'Ticket workflow status'),
('cr_priority', 'Ticket priority'),
('cr_returnstatus', 'Return logistics status'),
('cr_worktype', 'Worklog type'),
('cr_movementtype', 'Asset movement type'),
('cr_ownertype', 'Asset owner type'),
('cr_assetstatus', 'Asset state'),
('cr_outcome', 'Ticket outcome'),
('cr_languagemode', 'User language mode'),
('cr_language', 'Supported languages'),
('cr_partrequeststatus', 'Part request lifecycle'),
('cr_documenttype', 'Ticket document type'),
('cr_nctype', 'Non-conformity type'),
('cr_ncstatus', 'Non-conformity status');

INSERT INTO cr_choice_option (cr_choice_set_id, cr_value, cr_label, cr_sort_order)
SELECT id, 'Received', 'Received', 1 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'Diagnosis', 'Diagnosis', 2 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'WaitingParts', 'Waiting Parts', 3 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'RepairInProgress', 'Repair In Progress', 4 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'Testing', 'Testing', 5 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'ReadyToShip', 'Ready To Ship', 6 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'Shipped', 'Shipped', 7 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'Closed', 'Closed', 8 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'Cancelled', 'Cancelled', 9 FROM cr_choice_set WHERE cr_name='cr_ticketstatus'
UNION ALL SELECT id, 'Low', 'Low', 1 FROM cr_choice_set WHERE cr_name='cr_priority'
UNION ALL SELECT id, 'High', 'High', 2 FROM cr_choice_set WHERE cr_name='cr_priority'
UNION ALL SELECT id, 'Critical', 'Critical', 3 FROM cr_choice_set WHERE cr_name='cr_priority'
UNION ALL SELECT id, 'Planned', 'Planned', 1 FROM cr_choice_set WHERE cr_name='cr_returnstatus'
UNION ALL SELECT id, 'InTransit', 'In Transit', 2 FROM cr_choice_set WHERE cr_name='cr_returnstatus'
UNION ALL SELECT id, 'Delivered', 'Delivered', 3 FROM cr_choice_set WHERE cr_name='cr_returnstatus'
UNION ALL SELECT id, 'Diagnosis', 'Diagnosis', 1 FROM cr_choice_set WHERE cr_name='cr_worktype'
UNION ALL SELECT id, 'Repair', 'Repair', 2 FROM cr_choice_set WHERE cr_name='cr_worktype'
UNION ALL SELECT id, 'Testing', 'Testing', 3 FROM cr_choice_set WHERE cr_name='cr_worktype'
UNION ALL SELECT id, 'Rework', 'Rework', 4 FROM cr_choice_set WHERE cr_name='cr_worktype'
UNION ALL SELECT id, 'TransferOut', 'Transfer Out', 1 FROM cr_choice_set WHERE cr_name='cr_movementtype'
UNION ALL SELECT id, 'TransferIn', 'Transfer In', 2 FROM cr_choice_set WHERE cr_name='cr_movementtype'
UNION ALL SELECT id, 'ReturnOut', 'Return Out', 3 FROM cr_choice_set WHERE cr_name='cr_movementtype'
UNION ALL SELECT id, 'ReturnIn', 'Return In', 4 FROM cr_choice_set WHERE cr_name='cr_movementtype'
UNION ALL SELECT id, 'ShipToCustomer', 'Ship To Customer', 5 FROM cr_choice_set WHERE cr_name='cr_movementtype'
UNION ALL SELECT id, 'ReceiveFromCustomer', 'Receive From Customer', 6 FROM cr_choice_set WHERE cr_name='cr_movementtype'
UNION ALL SELECT id, 'Customer', 'Customer', 1 FROM cr_choice_set WHERE cr_name='cr_ownertype'
UNION ALL SELECT id, 'Company', 'Company', 2 FROM cr_choice_set WHERE cr_name='cr_ownertype'
UNION ALL SELECT id, 'InService', 'In Service', 1 FROM cr_choice_set WHERE cr_name='cr_assetstatus'
UNION ALL SELECT id, 'InRepair', 'In Repair', 2 FROM cr_choice_set WHERE cr_name='cr_assetstatus'
UNION ALL SELECT id, 'Quarantine', 'Quarantine', 3 FROM cr_choice_set WHERE cr_name='cr_assetstatus'
UNION ALL SELECT id, 'Scrapped', 'Scrapped', 4 FROM cr_choice_set WHERE cr_name='cr_assetstatus'
UNION ALL SELECT id, 'SpareStock', 'Spare Stock', 5 FROM cr_choice_set WHERE cr_name='cr_assetstatus'
UNION ALL SELECT id, 'RepairedReturned', 'Repaired Returned', 1 FROM cr_choice_set WHERE cr_name='cr_outcome'
UNION ALL SELECT id, 'ExchangePerformed', 'Exchange Performed', 2 FROM cr_choice_set WHERE cr_name='cr_outcome'
UNION ALL SELECT id, 'NonRepairable', 'Non Repairable', 3 FROM cr_choice_set WHERE cr_name='cr_outcome'
UNION ALL SELECT id, 'Cancelled', 'Cancelled', 4 FROM cr_choice_set WHERE cr_name='cr_outcome'
UNION ALL SELECT id, 'Other', 'Other', 5 FROM cr_choice_set WHERE cr_name='cr_outcome'
UNION ALL SELECT id, 'Auto', 'Auto', 1 FROM cr_choice_set WHERE cr_name='cr_languagemode'
UNION ALL SELECT id, 'Manual', 'Manual', 2 FROM cr_choice_set WHERE cr_name='cr_languagemode'
UNION ALL SELECT id, 'es', 'es', 1 FROM cr_choice_set WHERE cr_name='cr_language'
UNION ALL SELECT id, 'pt', 'pt', 2 FROM cr_choice_set WHERE cr_name='cr_language'
UNION ALL SELECT id, 'it', 'it', 3 FROM cr_choice_set WHERE cr_name='cr_language'
UNION ALL SELECT id, 'en', 'en', 4 FROM cr_choice_set WHERE cr_name='cr_language'
UNION ALL SELECT id, 'Requested', 'Requested', 1 FROM cr_choice_set WHERE cr_name='cr_partrequeststatus'
UNION ALL SELECT id, 'Approved', 'Approved', 2 FROM cr_choice_set WHERE cr_name='cr_partrequeststatus'
UNION ALL SELECT id, 'Ordered', 'Ordered', 3 FROM cr_choice_set WHERE cr_name='cr_partrequeststatus'
UNION ALL SELECT id, 'Received', 'Received', 4 FROM cr_choice_set WHERE cr_name='cr_partrequeststatus'
UNION ALL SELECT id, 'Assigned', 'Assigned', 5 FROM cr_choice_set WHERE cr_name='cr_partrequeststatus'
UNION ALL SELECT id, 'Cancelled', 'Cancelled', 6 FROM cr_choice_set WHERE cr_name='cr_partrequeststatus'
UNION ALL SELECT id, 'TransportDamagePhoto', 'Transport Damage Photo', 1 FROM cr_choice_set WHERE cr_name='cr_documenttype'
UNION ALL SELECT id, 'DiagnosticReport', 'Diagnostic Report', 2 FROM cr_choice_set WHERE cr_name='cr_documenttype'
UNION ALL SELECT id, 'BeforeAfterPhotos', 'Before/After Photos', 3 FROM cr_choice_set WHERE cr_name='cr_documenttype'
UNION ALL SELECT id, 'TestEvidence', 'Test Evidence', 4 FROM cr_choice_set WHERE cr_name='cr_documenttype'
UNION ALL SELECT id, 'ShippingDoc', 'Shipping Doc', 5 FROM cr_choice_set WHERE cr_name='cr_documenttype'
UNION ALL SELECT id, 'ExchangeDoc', 'Exchange Doc', 6 FROM cr_choice_set WHERE cr_name='cr_documenttype'
UNION ALL SELECT id, 'Other', 'Other', 7 FROM cr_choice_set WHERE cr_name='cr_documenttype'
UNION ALL SELECT id, 'Rework', 'Rework', 1 FROM cr_choice_set WHERE cr_name='cr_nctype'
UNION ALL SELECT id, 'TestFail', 'Test Fail', 2 FROM cr_choice_set WHERE cr_name='cr_nctype'
UNION ALL SELECT id, 'DocumentationError', 'Documentation Error', 3 FROM cr_choice_set WHERE cr_name='cr_nctype'
UNION ALL SELECT id, 'TransportDamage', 'Transport Damage', 4 FROM cr_choice_set WHERE cr_name='cr_nctype'
UNION ALL SELECT id, 'RepeatedFailure', 'Repeated Failure', 5 FROM cr_choice_set WHERE cr_name='cr_nctype'
UNION ALL SELECT id, 'Other', 'Other', 6 FROM cr_choice_set WHERE cr_name='cr_nctype'
UNION ALL SELECT id, 'Open', 'Open', 1 FROM cr_choice_set WHERE cr_name='cr_ncstatus'
UNION ALL SELECT id, 'Closed', 'Closed', 2 FROM cr_choice_set WHERE cr_name='cr_ncstatus';

INSERT INTO cr_site (cr_sitecode, cr_name, cr_country, cr_city, cr_defaultpriority, cr_defaultinitialstatus, cr_requiresolutiontoclose, cr_requirematerialstoclose, cr_requiretestingchecklisttoship)
VALUES
('ESP', 'Castellon Service Center', 'Espana', 'Castellon', 'High', 'Received', 1, 0, 1),
('MEX', 'Monterrey Service Center', 'Mexico', 'Monterrey NL', 'High', 'Received', 1, 1, 1),
('BRA', 'Rio Claro Service Center', 'Brasil', 'Rio Claro', 'Low', 'Received', 1, 0, 1),
('USA', 'Nashville Service Center', 'EEUU', 'Nashville, TN', 'Critical', 'Received', 1, 1, 1);

INSERT INTO cr_role (cr_name) VALUES
('Recepcion'),
('Tecnico'),
('Coordinacion'),
('Logistica'),
('Administracion'),
('Calidad');

INSERT INTO cr_prioritysla (cr_priority, cr_firstresponse_hours, cr_repairtarget_hours, cr_closuretarget_hours)
VALUES
('Low', 48, 120, 168),
('High', 8, 48, 72),
('Critical', 2, 24, 48);

INSERT INTO cr_localizationstring (cr_key, cr_text_es, cr_text_pt, cr_text_it, cr_text_en, cr_module, cr_isactive)
VALUES
('BTN_SAVE', 'Guardar', 'Salvar', 'Salva', 'Save', 'UI', 1),
('BTN_CANCEL', 'Cancelar', 'Cancelar', 'Annulla', 'Cancel', 'UI', 1),
('BTN_CLOSE', 'Cerrar', 'Fechar', 'Chiudi', 'Close', 'UI', 1),
('BTN_SUBMIT', 'Enviar', 'Enviar', 'Invia', 'Submit', 'UI', 1),
('LBL_STATUS', 'Estado', 'Status', 'Stato', 'Status', 'Ticket', 1),
('LBL_PRIORITY', 'Prioridad', 'Prioridade', 'Priorita', 'Priority', 'Ticket', 1),
('LBL_SITE', 'Sede', 'Unidade', 'Sede', 'Site', 'Core', 1),
('LBL_TICKETNUMBER', 'Numero de Ticket', 'Numero do Ticket', 'Numero Ticket', 'Ticket Number', 'Ticket', 1),
('LBL_SAPNOTICE', 'Aviso SAP', 'Aviso SAP', 'Avviso SAP', 'SAP Notice', 'Ticket', 1),
('LBL_SERIAL', 'Numero de Serie', 'Numero de Serie', 'Numero di Serie', 'Serial Number', 'Asset', 1),
('LBL_MODEL', 'Modelo', 'Modelo', 'Modello', 'Model', 'Asset', 1),
('LBL_CUSTOMER', 'Cliente', 'Cliente', 'Cliente', 'Customer', 'Core', 1),
('LBL_OWNER', 'Propietario', 'Proprietario', 'Proprietario', 'Owner', 'Asset', 1),
('LBL_LANGUAGE', 'Idioma', 'Idioma', 'Lingua', 'Language', 'Core', 1),
('MSG_REQUIRED', 'Campo obligatorio', 'Campo obrigatorio', 'Campo obbligatorio', 'Required field', 'Validation', 1),
('MSG_INVALID_STATUS', 'Estado invalido', 'Status invalido', 'Stato non valido', 'Invalid status', 'Validation', 1),
('MSG_TICKET_CREATED', 'Ticket creado', 'Ticket criado', 'Ticket creato', 'Ticket created', 'Ticket', 1),
('MSG_TICKET_UPDATED', 'Ticket actualizado', 'Ticket atualizado', 'Ticket aggiornato', 'Ticket updated', 'Ticket', 1),
('MSG_TICKET_CLOSED', 'Ticket cerrado', 'Ticket fechado', 'Ticket chiuso', 'Ticket closed', 'Ticket', 1),
('MSG_UPLOAD_OK', 'Archivo cargado', 'Arquivo enviado', 'File caricato', 'File uploaded', 'Document', 1);

INSERT INTO cr_accountplant (cr_name, cr_code, cr_isactive)
VALUES
('ACME Valencia Plant', 'ACME-ESP-01', 1),
('Globex Monterrey Plant', 'GLOBEX-MEX-02', 1);

INSERT INTO cr_person (cr_fullname, cr_email, cr_phone, cr_isactive)
VALUES
('Ana Recepcion', 'ana.recepcion@demo.local', '+34 600 000 001', 1),
('Bruno Tecnico', 'bruno.tecnico@demo.local', '+52 600 000 002', 1),
('Carla Coordinacion', 'carla.coordinacion@demo.local', '+55 600 000 003', 1),
('Diego Logistica', 'diego.logistica@demo.local', '+1 600 000 004', 1);

INSERT INTO cr_person_role_site (cr_person_id, cr_role_id, cr_site_id, cr_startdate, cr_enddate, cr_isprimary, cr_level)
SELECT p.id, r.id, s.id, '2026-01-01', NULL, 1, 'Operator'
FROM cr_person p
JOIN cr_role r ON (
  (p.cr_email='ana.recepcion@demo.local' AND r.cr_name='Recepcion') OR
  (p.cr_email='bruno.tecnico@demo.local' AND r.cr_name='Tecnico') OR
  (p.cr_email='carla.coordinacion@demo.local' AND r.cr_name='Coordinacion') OR
  (p.cr_email='diego.logistica@demo.local' AND r.cr_name='Logistica')
)
JOIN cr_site s ON (
  (p.cr_email='ana.recepcion@demo.local' AND s.cr_sitecode='ESP') OR
  (p.cr_email='bruno.tecnico@demo.local' AND s.cr_sitecode='MEX') OR
  (p.cr_email='carla.coordinacion@demo.local' AND s.cr_sitecode='BRA') OR
  (p.cr_email='diego.logistica@demo.local' AND s.cr_sitecode='USA')
);

INSERT INTO cr_usersettings (cr_useremail, cr_languagemode, cr_preferredlanguage, cr_defaultsite_id, cr_defaultview, cr_notificationsenabled)
SELECT 'ana.recepcion@demo.local', 'Manual', 'es', id, 'tickets_inbox', 1 FROM cr_site WHERE cr_sitecode='ESP'
UNION ALL SELECT 'bruno.tecnico@demo.local', 'Manual', 'en', id, 'my_worklog', 1 FROM cr_site WHERE cr_sitecode='MEX'
UNION ALL SELECT 'carla.coordinacion@demo.local', 'Auto', 'pt', id, 'coord_board', 1 FROM cr_site WHERE cr_sitecode='BRA'
UNION ALL SELECT 'diego.logistica@demo.local', 'Manual', 'en', id, 'shipments', 1 FROM cr_site WHERE cr_sitecode='USA';

INSERT INTO cr_asset (cr_assetcode, cr_serialnumber, cr_model, cr_ownertype, cr_owneraccountplant_id, cr_assetstatus, cr_currentsite_id, cr_notes, cr_isactive)
SELECT 'AST-CUST-0001', 'SN-CUST-0001', 'Pump-X100', 'Customer', ap.id, 'InRepair', s.id, 'Activo de cliente en reparacion', 1
FROM cr_accountplant ap
JOIN cr_site s ON s.cr_sitecode='ESP'
WHERE ap.cr_code='ACME-ESP-01'
UNION ALL
SELECT 'AST-COMP-0001', 'SN-COMP-0001', 'Pump-X100-R', 'Company', ap.id, 'SpareStock', s.id, 'Activo de compania para reemplazo', 1
FROM cr_accountplant ap
JOIN cr_site s ON s.cr_sitecode='MEX'
WHERE ap.cr_code='GLOBEX-MEX-02';

INSERT INTO cr_ticket (
  cr_ticketnumber, cr_sitein_id, cr_currentsite_id, cr_closingsite_id, cr_sapnotice, cr_accountplant_id, cr_asset_id,
  cr_reportedfailure, cr_detectedfailure, cr_repairsolution, cr_priority, cr_status,
  cr_sla_duedate, cr_receiveddate, cr_closeddate, cr_nonrepairable, cr_warranty, cr_cancelreason,
  cr_technicalclosureready, cr_technicalclosuredate, cr_technicalclosureby,
  cr_administrativeclosuredone, cr_administrativeclosuredate, cr_administrativeclosureby,
  cr_returnrequired, cr_returnstatus, cr_returntargetdate, cr_outcome, cr_isactive
)
SELECT
  'TK-2026-000001',
  s_esp.id,
  s_mex.id,
  NULL,
  'SAP-900001',
  ap.id,
  a.id,
  'Baja presion reportada por cliente',
  'Sello interno desgastado',
  'Cambio de sello y recalibracion',
  'High',
  'RepairInProgress',
  '2026-02-24 18:00:00',
  '2026-02-21 09:00:00',
  NULL,
  0,
  1,
  NULL,
  0,
  NULL,
  NULL,
  0,
  NULL,
  NULL,
  1,
  'InTransit',
  '2026-02-26 18:00:00',
  NULL,
  1
FROM cr_site s_esp
JOIN cr_site s_mex ON s_mex.cr_sitecode='MEX'
JOIN cr_accountplant ap ON ap.cr_code='ACME-ESP-01'
JOIN cr_asset a ON a.cr_serialnumber='SN-CUST-0001'
WHERE s_esp.cr_sitecode='ESP';

INSERT INTO cr_tickethistory (cr_ticket_id, cr_fromstatus, cr_tostatus, cr_siteattime_id, cr_changedby, cr_changedon, cr_comment)
SELECT t.id, NULL, 'Received', s.id, 'ana.recepcion@demo.local', '2026-02-21 09:05:00', 'Recepcion inicial del equipo'
FROM cr_ticket t JOIN cr_site s ON s.cr_sitecode='ESP' WHERE t.cr_ticketnumber='TK-2026-000001'
UNION ALL
SELECT t.id, 'Received', 'Diagnosis', s.id, 'bruno.tecnico@demo.local', '2026-02-21 10:00:00', 'Se inicia diagnostico tecnico'
FROM cr_ticket t JOIN cr_site s ON s.cr_sitecode='ESP' WHERE t.cr_ticketnumber='TK-2026-000001'
UNION ALL
SELECT t.id, 'Diagnosis', 'RepairInProgress', s.id, 'bruno.tecnico@demo.local', '2026-02-21 14:00:00', 'Reparacion iniciada con repuesto interno'
FROM cr_ticket t JOIN cr_site s ON s.cr_sitecode='MEX' WHERE t.cr_ticketnumber='TK-2026-000001';

INSERT INTO cr_worklog (cr_ticket_id, cr_worktype, cr_sitework_id, cr_technician_id, cr_start, cr_end, cr_result, cr_notes)
SELECT t.id, 'Diagnosis', s.id, p.id, '2026-02-21 10:00:00', '2026-02-21 12:00:00', 'RootCauseFound', 'Fuga detectada en sello interno'
FROM cr_ticket t
JOIN cr_site s ON s.cr_sitecode='ESP'
JOIN cr_person p ON p.cr_email='bruno.tecnico@demo.local'
WHERE t.cr_ticketnumber='TK-2026-000001';

INSERT INTO cr_ticketmaterial (cr_ticket_id, cr_partname, cr_quantity, cr_usedby_id, cr_usedon, cr_comment)
SELECT t.id, 'Seal Kit X100', 1.00, p.id, '2026-02-21 14:20:00', 'Instalado durante reparacion'
FROM cr_ticket t
JOIN cr_person p ON p.cr_email='bruno.tecnico@demo.local'
WHERE t.cr_ticketnumber='TK-2026-000001';

INSERT INTO cr_movement (cr_ticket_id, cr_asset_id, cr_movementtype, cr_fromsite_id, cr_tosite_id, cr_datetime, cr_reference, cr_executedby, cr_notes)
SELECT t.id, a.id, 'TransferOut', s1.id, s2.id, '2026-02-21 13:00:00', 'MOVE-2026-00001', 'diego.logistica@demo.local', 'Transferencia de ESP a MEX para reparacion'
FROM cr_ticket t
JOIN cr_asset a ON a.id=t.cr_asset_id
JOIN cr_site s1 ON s1.cr_sitecode='ESP'
JOIN cr_site s2 ON s2.cr_sitecode='MEX'
WHERE t.cr_ticketnumber='TK-2026-000001';

DELIMITER $$

CREATE TRIGGER trg_cr_ticket_before_update_immutable_sitein
BEFORE UPDATE ON cr_ticket
FOR EACH ROW
BEGIN
  IF NEW.cr_sitein_id <> OLD.cr_sitein_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'cr_sitein_id is immutable';
  END IF;
END$$

CREATE TRIGGER trg_cr_tickethistory_before_update
BEFORE UPDATE ON cr_tickethistory
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'cr_tickethistory is append-only';
END$$

CREATE TRIGGER trg_cr_tickethistory_before_delete
BEFORE DELETE ON cr_tickethistory
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_tickethistory';
END$$

CREATE TRIGGER trg_cr_ticket_before_delete
BEFORE DELETE ON cr_ticket
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_ticket';
END$$

CREATE TRIGGER trg_cr_asset_before_delete
BEFORE DELETE ON cr_asset
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_asset';
END$$

CREATE TRIGGER trg_cr_worklog_before_delete
BEFORE DELETE ON cr_worklog
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_worklog';
END$$

CREATE TRIGGER trg_cr_movement_before_delete
BEFORE DELETE ON cr_movement
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_movement';
END$$

CREATE TRIGGER trg_cr_ticketmaterial_before_delete
BEFORE DELETE ON cr_ticketmaterial
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_ticketmaterial';
END$$

CREATE TRIGGER trg_cr_assetexchange_before_delete
BEFORE DELETE ON cr_assetexchange
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_assetexchange';
END$$

CREATE TRIGGER trg_cr_ticketdocument_before_delete
BEFORE DELETE ON cr_ticketdocument
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_ticketdocument';
END$$

CREATE TRIGGER trg_cr_person_before_delete
BEFORE DELETE ON cr_person
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_person';
END$$

CREATE TRIGGER trg_cr_usersettings_before_delete
BEFORE DELETE ON cr_usersettings
FOR EACH ROW
BEGIN
  SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is not allowed on cr_usersettings';
END$$

CREATE TRIGGER trg_cr_ticket_ai_audit
AFTER INSERT ON cr_ticket
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_ticket', NEW.id, 'INSERT', NEW.cr_technicalclosureby, JSON_OBJECT('cr_ticketnumber', NEW.cr_ticketnumber, 'cr_status', NEW.cr_status));
END$$

CREATE TRIGGER trg_cr_ticket_au_audit
AFTER UPDATE ON cr_ticket
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_ticket', NEW.id, 'UPDATE', NEW.cr_technicalclosureby, JSON_OBJECT('old_status', OLD.cr_status, 'new_status', NEW.cr_status));
END$$

CREATE TRIGGER trg_cr_asset_ai_audit
AFTER INSERT ON cr_asset
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_payload)
  VALUES ('cr_asset', NEW.id, 'INSERT', JSON_OBJECT('cr_serialnumber', NEW.cr_serialnumber, 'cr_assetstatus', NEW.cr_assetstatus));
END$$

CREATE TRIGGER trg_cr_asset_au_audit
AFTER UPDATE ON cr_asset
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_payload)
  VALUES ('cr_asset', NEW.id, 'UPDATE', JSON_OBJECT('old_status', OLD.cr_assetstatus, 'new_status', NEW.cr_assetstatus));
END$$

CREATE TRIGGER trg_cr_tickethistory_ai_audit
AFTER INSERT ON cr_tickethistory
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_tickethistory', NEW.id, 'INSERT', NEW.cr_changedby, JSON_OBJECT('cr_ticket_id', NEW.cr_ticket_id, 'cr_tostatus', NEW.cr_tostatus));
END$$

CREATE TRIGGER trg_cr_worklog_ai_audit
AFTER INSERT ON cr_worklog
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_payload)
  VALUES ('cr_worklog', NEW.id, 'INSERT', JSON_OBJECT('cr_ticket_id', NEW.cr_ticket_id, 'cr_worktype', NEW.cr_worktype));
END$$

CREATE TRIGGER trg_cr_worklog_au_audit
AFTER UPDATE ON cr_worklog
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_payload)
  VALUES ('cr_worklog', NEW.id, 'UPDATE', JSON_OBJECT('old_end', OLD.cr_end, 'new_end', NEW.cr_end));
END$$

CREATE TRIGGER trg_cr_movement_ai_audit
AFTER INSERT ON cr_movement
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_movement', NEW.id, 'INSERT', NEW.cr_executedby, JSON_OBJECT('cr_movementtype', NEW.cr_movementtype, 'cr_reference', NEW.cr_reference));
END$$

CREATE TRIGGER trg_cr_ticketmaterial_ai_audit
AFTER INSERT ON cr_ticketmaterial
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_payload)
  VALUES ('cr_ticketmaterial', NEW.id, 'INSERT', JSON_OBJECT('cr_ticket_id', NEW.cr_ticket_id, 'cr_partname', NEW.cr_partname, 'cr_quantity', NEW.cr_quantity));
END$$

CREATE TRIGGER trg_cr_assetexchange_ai_audit
AFTER INSERT ON cr_assetexchange
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_assetexchange', NEW.id, 'INSERT', NEW.cr_approvedby, JSON_OBJECT('cr_ticket_id', NEW.cr_ticket_id, 'cr_exchangedate', NEW.cr_exchangedate));
END$$

CREATE TRIGGER trg_cr_ticketdocument_ai_audit
AFTER INSERT ON cr_ticketdocument
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_ticketdocument', NEW.id, 'INSERT', NEW.cr_author, JSON_OBJECT('cr_ticket_id', NEW.cr_ticket_id, 'cr_documenttype', NEW.cr_documenttype));
END$$

CREATE TRIGGER trg_cr_person_ai_audit
AFTER INSERT ON cr_person
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_person', NEW.id, 'INSERT', NEW.cr_email, JSON_OBJECT('cr_fullname', NEW.cr_fullname));
END$$

CREATE TRIGGER trg_cr_person_au_audit
AFTER UPDATE ON cr_person
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_person', NEW.id, 'UPDATE', NEW.cr_email, JSON_OBJECT('old_isactive', OLD.cr_isactive, 'new_isactive', NEW.cr_isactive));
END$$

CREATE TRIGGER trg_cr_usersettings_ai_audit
AFTER INSERT ON cr_usersettings
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_usersettings', NEW.id, 'INSERT', NEW.cr_useremail, JSON_OBJECT('cr_preferredlanguage', NEW.cr_preferredlanguage));
END$$

CREATE TRIGGER trg_cr_usersettings_au_audit
AFTER UPDATE ON cr_usersettings
FOR EACH ROW
BEGIN
  INSERT INTO cr_auditlog (cr_tablename, cr_recordid, cr_action, cr_changedby, cr_payload)
  VALUES ('cr_usersettings', NEW.id, 'UPDATE', NEW.cr_useremail, JSON_OBJECT('old_lang', OLD.cr_preferredlanguage, 'new_lang', NEW.cr_preferredlanguage));
END$$

DELIMITER ;
