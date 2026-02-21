SET NAMES utf8mb4;

-- ========================================================
-- Fase 2 - Seguridad, alcance multi-sede y gobernanza ISO
-- Requiere esquema de Fase 1 previamente creado.
-- ========================================================

-- -----------------------------
-- 1) Catálogo de roles seguridad
-- -----------------------------
CREATE TABLE IF NOT EXISTS cr_security_role (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_name VARCHAR(80) NOT NULL,
  cr_description VARCHAR(255) NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_security_role_name (cr_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cr_security_role (cr_name, cr_description)
VALUES
('cr_AdminSistema', 'Control total de seguridad, catalogos y mantenimiento'),
('cr_Coordinacion', 'Coordina operacion, asignaciones y control de SLA'),
('cr_Calidad', 'Gobernanza ISO, NC, evidencia y bloqueo de cierres'),
('cr_Tecnico', 'Ejecucion tecnica de diagnostico/reparacion/pruebas'),
('cr_Recepcion', 'Alta de ticket y validacion de ingreso'),
('cr_Logistica', 'Traslados, retorno y despacho'),
('cr_Administracion', 'Cierre administrativo y control documental')
ON DUPLICATE KEY UPDATE cr_description = VALUES(cr_description), cr_isactive = 1;

-- -----------------------------
-- 2) Permisos CRUD por tabla x rol
-- -----------------------------
CREATE TABLE IF NOT EXISTS cr_security_table_permission (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_role_id BIGINT UNSIGNED NOT NULL,
  cr_resource VARCHAR(64) NOT NULL,
  cr_can_create TINYINT(1) NOT NULL DEFAULT 0,
  cr_can_read TINYINT(1) NOT NULL DEFAULT 0,
  cr_can_update TINYINT(1) NOT NULL DEFAULT 0,
  cr_can_delete TINYINT(1) NOT NULL DEFAULT 0,
  cr_can_change_status TINYINT(1) NOT NULL DEFAULT 0,
  cr_can_close_technical TINYINT(1) NOT NULL DEFAULT 0,
  cr_can_close_administrative TINYINT(1) NOT NULL DEFAULT 0,
  cr_can_approve TINYINT(1) NOT NULL DEFAULT 0,
  cr_can_manage_sla TINYINT(1) NOT NULL DEFAULT 0,
  cr_scope ENUM('OWN_SITE', 'OWN_SITE_PLUS_RELATED', 'ALL_SITES') NOT NULL DEFAULT 'OWN_SITE',
  cr_notes VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_sec_perm_role_resource (cr_role_id, cr_resource),
  CONSTRAINT fk_cr_sec_perm_role FOREIGN KEY (cr_role_id) REFERENCES cr_security_role(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE p
FROM cr_security_table_permission p
JOIN cr_security_role r ON r.id = p.cr_role_id
WHERE r.cr_name IN ('cr_AdminSistema', 'cr_Coordinacion', 'cr_Calidad', 'cr_Tecnico', 'cr_Recepcion', 'cr_Logistica', 'cr_Administracion');

INSERT INTO cr_security_table_permission
(cr_role_id, cr_resource, cr_can_create, cr_can_read, cr_can_update, cr_can_delete, cr_can_change_status, cr_can_close_technical, cr_can_close_administrative, cr_can_approve, cr_can_manage_sla, cr_scope, cr_notes)
SELECT r.id, x.cr_resource, x.cr_can_create, x.cr_can_read, x.cr_can_update, x.cr_can_delete, x.cr_can_change_status, x.cr_can_close_technical, x.cr_can_close_administrative, x.cr_can_approve, x.cr_can_manage_sla, x.cr_scope, x.cr_notes
FROM cr_security_role r
JOIN (
  -- AdminSistema (todo)
  SELECT 'cr_AdminSistema' AS role_name, '*' AS cr_resource, 1 AS cr_can_create, 1 AS cr_can_read, 1 AS cr_can_update, 1 AS cr_can_delete, 1 AS cr_can_change_status, 1 AS cr_can_close_technical, 1 AS cr_can_close_administrative, 1 AS cr_can_approve, 1 AS cr_can_manage_sla, 'ALL_SITES' AS cr_scope, 'Superusuario' AS cr_notes

  UNION ALL SELECT 'cr_Coordinacion', 'cr_ticket', 1,1,1,0,1,1,0,1,1,'ALL_SITES','Asigna y controla ciclo tecnico'
  UNION ALL SELECT 'cr_Coordinacion', 'cr_tickethistory', 1,1,0,0,0,0,0,0,0,'ALL_SITES','Append-only'
  UNION ALL SELECT 'cr_Coordinacion', 'cr_worklog', 1,1,1,0,0,0,0,0,0,'ALL_SITES','Gestion operacional'
  UNION ALL SELECT 'cr_Coordinacion', 'cr_ticketmaterial', 1,1,1,0,0,0,0,0,0,'ALL_SITES','Validacion de consumo'
  UNION ALL SELECT 'cr_Coordinacion', 'cr_movement', 1,1,1,0,0,0,0,1,0,'ALL_SITES','Autoriza transferencias'
  UNION ALL SELECT 'cr_Coordinacion', 'cr_partrequest', 1,1,1,0,0,0,0,1,0,'ALL_SITES','Aprobacion de partes'
  UNION ALL SELECT 'cr_Coordinacion', 'cr_assetexchange', 1,1,1,0,0,0,0,1,0,'ALL_SITES','Aprueba no reparable/exchange'

  UNION ALL SELECT 'cr_Calidad', 'cr_ticket', 0,1,0,0,1,0,0,1,0,'ALL_SITES','Bloqueo de cierre por evidencia'
  UNION ALL SELECT 'cr_Calidad', 'cr_tickethistory', 1,1,0,0,0,0,0,0,0,'ALL_SITES','Solo anexar comentarios/auditoria'
  UNION ALL SELECT 'cr_Calidad', 'cr_nonconformity', 1,1,1,0,0,0,0,1,0,'ALL_SITES','ISO 10.2'
  UNION ALL SELECT 'cr_Calidad', 'cr_ticketdocument', 1,1,1,0,0,0,0,1,0,'ALL_SITES','Gestion de evidencias'
  UNION ALL SELECT 'cr_Calidad', 'cr_localizationstring', 1,1,1,0,0,0,0,0,0,'ALL_SITES','Gobernanza traducciones'
  UNION ALL SELECT 'cr_Calidad', 'cr_choice_set', 1,1,1,0,0,0,0,0,0,'ALL_SITES','Catalogos gobernados'
  UNION ALL SELECT 'cr_Calidad', 'cr_choice_option', 1,1,1,0,0,0,0,0,0,'ALL_SITES','Catalogos gobernados'

  UNION ALL SELECT 'cr_Tecnico', 'cr_ticket', 0,1,0,0,1,0,0,0,0,'OWN_SITE_PLUS_RELATED','No edita SLA/catalogos'
  UNION ALL SELECT 'cr_Tecnico', 'cr_tickethistory', 1,1,0,0,0,0,0,0,0,'OWN_SITE_PLUS_RELATED','Append-only'
  UNION ALL SELECT 'cr_Tecnico', 'cr_worklog', 1,1,1,0,0,0,0,0,0,'OWN_SITE_PLUS_RELATED','Operacion tecnica principal'
  UNION ALL SELECT 'cr_Tecnico', 'cr_ticketmaterial', 1,1,1,0,0,0,0,0,0,'OWN_SITE_PLUS_RELATED','Consumo de materiales'
  UNION ALL SELECT 'cr_Tecnico', 'cr_ticketdocument', 1,1,1,0,0,0,0,0,0,'OWN_SITE_PLUS_RELATED','Evidencia tecnica'

  UNION ALL SELECT 'cr_Recepcion', 'cr_ticket', 1,1,1,0,1,0,0,0,0,'OWN_SITE','Alta e ingreso'
  UNION ALL SELECT 'cr_Recepcion', 'cr_tickethistory', 1,1,0,0,0,0,0,0,0,'OWN_SITE','Append-only'
  UNION ALL SELECT 'cr_Recepcion', 'cr_ticketdocument', 1,1,1,0,0,0,0,0,0,'OWN_SITE','Evidencia de recepcion'
  UNION ALL SELECT 'cr_Recepcion', 'cr_asset', 1,1,1,0,0,0,0,0,0,'OWN_SITE','Validacion de activo de ingreso'

  UNION ALL SELECT 'cr_Logistica', 'cr_ticket', 0,1,0,0,1,0,0,0,0,'OWN_SITE_PLUS_RELATED','Solo estados logisticos'
  UNION ALL SELECT 'cr_Logistica', 'cr_movement', 1,1,1,0,0,0,0,0,0,'OWN_SITE_PLUS_RELATED','Transfer/Return/Ship'
  UNION ALL SELECT 'cr_Logistica', 'cr_ticketdocument', 1,1,1,0,0,0,0,0,0,'OWN_SITE_PLUS_RELATED','Documentos de envio'

  UNION ALL SELECT 'cr_Administracion', 'cr_ticket', 0,1,1,0,0,0,1,0,0,'ALL_SITES','Cierre administrativo'
  UNION ALL SELECT 'cr_Administracion', 'cr_tickethistory', 1,1,0,0,0,0,0,0,0,'ALL_SITES','Append-only'
  UNION ALL SELECT 'cr_Administracion', 'cr_ticketdocument', 1,1,1,0,0,0,0,0,0,'ALL_SITES','Revision documental'
) x ON x.role_name = r.cr_name;

-- -----------------------------
-- 3) Asignación usuario->rol
-- -----------------------------
CREATE TABLE IF NOT EXISTS cr_security_user_role (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_useremail VARCHAR(191) NOT NULL,
  cr_role_id BIGINT UNSIGNED NOT NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  cr_startdate DATE NOT NULL,
  cr_enddate DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_security_user_role (cr_useremail, cr_role_id, cr_startdate),
  KEY idx_cr_security_user_role_user (cr_useremail),
  CONSTRAINT fk_cr_security_user_role_role FOREIGN KEY (cr_role_id) REFERENCES cr_security_role(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed básico con usuarios fase 1
INSERT INTO cr_security_user_role (cr_useremail, cr_role_id, cr_isactive, cr_startdate, cr_enddate)
SELECT 'ana.recepcion@demo.local', id, 1, '2026-02-21', NULL FROM cr_security_role WHERE cr_name='cr_Recepcion'
UNION ALL SELECT 'bruno.tecnico@demo.local', id, 1, '2026-02-21', NULL FROM cr_security_role WHERE cr_name='cr_Tecnico'
UNION ALL SELECT 'carla.coordinacion@demo.local', id, 1, '2026-02-21', NULL FROM cr_security_role WHERE cr_name='cr_Coordinacion'
UNION ALL SELECT 'diego.logistica@demo.local', id, 1, '2026-02-21', NULL FROM cr_security_role WHERE cr_name='cr_Logistica'
ON DUPLICATE KEY UPDATE cr_isactive = VALUES(cr_isactive), cr_enddate = VALUES(cr_enddate);

-- -----------------------------
-- 4) Teams por sede (recomendado)
-- -----------------------------
CREATE TABLE IF NOT EXISTS cr_team (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_teamcode VARCHAR(32) NOT NULL,
  cr_name VARCHAR(120) NOT NULL,
  cr_site_id BIGINT UNSIGNED NOT NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_team_teamcode (cr_teamcode),
  KEY idx_cr_team_site (cr_site_id),
  CONSTRAINT fk_cr_team_site FOREIGN KEY (cr_site_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cr_team (cr_teamcode, cr_name, cr_site_id, cr_isactive)
SELECT CONCAT('TEAM_', s.cr_sitecode), CONCAT('Team ', s.cr_sitecode), s.id, 1
FROM cr_site s
ON DUPLICATE KEY UPDATE cr_name = VALUES(cr_name), cr_site_id = VALUES(cr_site_id), cr_isactive = 1;

CREATE TABLE IF NOT EXISTS cr_team_member (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_team_id BIGINT UNSIGNED NOT NULL,
  cr_useremail VARCHAR(191) NOT NULL,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  cr_startdate DATE NOT NULL,
  cr_enddate DATE NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_team_member (cr_team_id, cr_useremail, cr_startdate),
  KEY idx_cr_team_member_user (cr_useremail),
  CONSTRAINT fk_cr_team_member_team FOREIGN KEY (cr_team_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO cr_team_member (cr_team_id, cr_useremail, cr_isactive, cr_startdate, cr_enddate)
SELECT t.id, 'ana.recepcion@demo.local', 1, '2026-02-21', NULL FROM cr_team t WHERE t.cr_teamcode='TEAM_ESP'
UNION ALL SELECT t.id, 'bruno.tecnico@demo.local', 1, '2026-02-21', NULL FROM cr_team t WHERE t.cr_teamcode='TEAM_MEX'
UNION ALL SELECT t.id, 'carla.coordinacion@demo.local', 1, '2026-02-21', NULL FROM cr_team t WHERE t.cr_teamcode='TEAM_BRA'
UNION ALL SELECT t.id, 'diego.logistica@demo.local', 1, '2026-02-21', NULL FROM cr_team t WHERE t.cr_teamcode='TEAM_USA'
ON DUPLICATE KEY UPDATE cr_isactive = VALUES(cr_isactive), cr_enddate = VALUES(cr_enddate);

-- Scope adicional por sitio para usuarios sin team directo
CREATE TABLE IF NOT EXISTS cr_security_user_site_scope (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_useremail VARCHAR(191) NOT NULL,
  cr_site_id BIGINT UNSIGNED NOT NULL,
  cr_can_read TINYINT(1) NOT NULL DEFAULT 1,
  cr_can_write TINYINT(1) NOT NULL DEFAULT 0,
  cr_isactive TINYINT(1) NOT NULL DEFAULT 1,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_security_user_site_scope (cr_useremail, cr_site_id),
  CONSTRAINT fk_cr_security_user_scope_site FOREIGN KEY (cr_site_id) REFERENCES cr_site(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- -----------------------------
-- 5) Propietario de team en tablas transaccionales
-- -----------------------------
-- Nota: este bloque ALTER está diseñado para ejecutarse una sola vez
-- sobre una base que ya tiene esquema Fase 1 sin columnas cr_ownerteam_id.
-- Importante: Fase 1 define cr_tickethistory append-only. Para backfill
-- se elimina temporalmente el trigger de UPDATE y se vuelve a crear en la
-- seccion de reglas ISO al final de este script.
DROP TRIGGER IF EXISTS trg_cr_tickethistory_before_update;
SET @db_name = DATABASE();

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_asset' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_asset ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_ticket' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_ticket ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_tickethistory' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_tickethistory ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_worklog' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_worklog ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_ticketmaterial' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_ticketmaterial ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_partrequest' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_partrequest ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_movement' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_movement ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_ticketdocument' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_ticketdocument ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_assetexchange' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_assetexchange ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_nonconformity' AND COLUMN_NAME='cr_ownerteam_id')=0, 'ALTER TABLE cr_nonconformity ADD COLUMN cr_ownerteam_id BIGINT UNSIGNED NULL', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_asset' AND INDEX_NAME='idx_cr_asset_ownerteam')=0, 'ALTER TABLE cr_asset ADD KEY idx_cr_asset_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_ticket' AND INDEX_NAME='idx_cr_ticket_ownerteam')=0, 'ALTER TABLE cr_ticket ADD KEY idx_cr_ticket_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_tickethistory' AND INDEX_NAME='idx_cr_tickethistory_ownerteam')=0, 'ALTER TABLE cr_tickethistory ADD KEY idx_cr_tickethistory_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_worklog' AND INDEX_NAME='idx_cr_worklog_ownerteam')=0, 'ALTER TABLE cr_worklog ADD KEY idx_cr_worklog_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_ticketmaterial' AND INDEX_NAME='idx_cr_ticketmaterial_ownerteam')=0, 'ALTER TABLE cr_ticketmaterial ADD KEY idx_cr_ticketmaterial_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_partrequest' AND INDEX_NAME='idx_cr_partrequest_ownerteam')=0, 'ALTER TABLE cr_partrequest ADD KEY idx_cr_partrequest_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_movement' AND INDEX_NAME='idx_cr_movement_ownerteam')=0, 'ALTER TABLE cr_movement ADD KEY idx_cr_movement_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_ticketdocument' AND INDEX_NAME='idx_cr_ticketdocument_ownerteam')=0, 'ALTER TABLE cr_ticketdocument ADD KEY idx_cr_ticketdocument_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_assetexchange' AND INDEX_NAME='idx_cr_assetexchange_ownerteam')=0, 'ALTER TABLE cr_assetexchange ADD KEY idx_cr_assetexchange_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.STATISTICS WHERE TABLE_SCHEMA=@db_name AND TABLE_NAME='cr_nonconformity' AND INDEX_NAME='idx_cr_nonconformity_ownerteam')=0, 'ALTER TABLE cr_nonconformity ADD KEY idx_cr_nonconformity_ownerteam (cr_ownerteam_id)', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_asset' AND CONSTRAINT_NAME='fk_cr_asset_ownerteam')=0, 'ALTER TABLE cr_asset ADD CONSTRAINT fk_cr_asset_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_ticket' AND CONSTRAINT_NAME='fk_cr_ticket_ownerteam')=0, 'ALTER TABLE cr_ticket ADD CONSTRAINT fk_cr_ticket_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_tickethistory' AND CONSTRAINT_NAME='fk_cr_tickethistory_ownerteam')=0, 'ALTER TABLE cr_tickethistory ADD CONSTRAINT fk_cr_tickethistory_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_worklog' AND CONSTRAINT_NAME='fk_cr_worklog_ownerteam')=0, 'ALTER TABLE cr_worklog ADD CONSTRAINT fk_cr_worklog_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_ticketmaterial' AND CONSTRAINT_NAME='fk_cr_ticketmaterial_ownerteam')=0, 'ALTER TABLE cr_ticketmaterial ADD CONSTRAINT fk_cr_ticketmaterial_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_partrequest' AND CONSTRAINT_NAME='fk_cr_partrequest_ownerteam')=0, 'ALTER TABLE cr_partrequest ADD CONSTRAINT fk_cr_partrequest_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_movement' AND CONSTRAINT_NAME='fk_cr_movement_ownerteam')=0, 'ALTER TABLE cr_movement ADD CONSTRAINT fk_cr_movement_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_ticketdocument' AND CONSTRAINT_NAME='fk_cr_ticketdocument_ownerteam')=0, 'ALTER TABLE cr_ticketdocument ADD CONSTRAINT fk_cr_ticketdocument_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_assetexchange' AND CONSTRAINT_NAME='fk_cr_assetexchange_ownerteam')=0, 'ALTER TABLE cr_assetexchange ADD CONSTRAINT fk_cr_assetexchange_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;
SET @sql = IF((SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLE_CONSTRAINTS WHERE CONSTRAINT_SCHEMA=@db_name AND TABLE_NAME='cr_nonconformity' AND CONSTRAINT_NAME='fk_cr_nonconformity_ownerteam')=0, 'ALTER TABLE cr_nonconformity ADD CONSTRAINT fk_cr_nonconformity_ownerteam FOREIGN KEY (cr_ownerteam_id) REFERENCES cr_team(id) ON DELETE RESTRICT ON UPDATE CASCADE', 'SELECT 1');
PREPARE stmt FROM @sql; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- Backfill owner team
UPDATE cr_asset a
LEFT JOIN cr_team t ON t.cr_site_id = a.cr_currentsite_id
SET a.cr_ownerteam_id = COALESCE(a.cr_ownerteam_id, t.id);

UPDATE cr_ticket tk
LEFT JOIN cr_team t ON t.cr_site_id = tk.cr_currentsite_id
SET tk.cr_ownerteam_id = COALESCE(tk.cr_ownerteam_id, t.id);

UPDATE cr_tickethistory th
JOIN cr_ticket tk ON tk.id = th.cr_ticket_id
SET th.cr_ownerteam_id = COALESCE(th.cr_ownerteam_id, tk.cr_ownerteam_id);

UPDATE cr_worklog wl
LEFT JOIN cr_team t ON t.cr_site_id = wl.cr_sitework_id
SET wl.cr_ownerteam_id = COALESCE(wl.cr_ownerteam_id, t.id);

UPDATE cr_ticketmaterial tm
JOIN cr_ticket tk ON tk.id = tm.cr_ticket_id
SET tm.cr_ownerteam_id = COALESCE(tm.cr_ownerteam_id, tk.cr_ownerteam_id);

UPDATE cr_partrequest pr
JOIN cr_ticket tk ON tk.id = pr.cr_ticket_id
SET pr.cr_ownerteam_id = COALESCE(pr.cr_ownerteam_id, tk.cr_ownerteam_id);

UPDATE cr_movement mv
LEFT JOIN cr_ticket tk ON tk.id = mv.cr_ticket_id
LEFT JOIN cr_team t_to ON t_to.cr_site_id = mv.cr_tosite_id
LEFT JOIN cr_team t_from ON t_from.cr_site_id = mv.cr_fromsite_id
SET mv.cr_ownerteam_id = COALESCE(mv.cr_ownerteam_id, tk.cr_ownerteam_id, t_to.id, t_from.id);

UPDATE cr_ticketdocument td
JOIN cr_ticket tk ON tk.id = td.cr_ticket_id
SET td.cr_ownerteam_id = COALESCE(td.cr_ownerteam_id, tk.cr_ownerteam_id);

UPDATE cr_assetexchange ae
JOIN cr_ticket tk ON tk.id = ae.cr_ticket_id
SET ae.cr_ownerteam_id = COALESCE(ae.cr_ownerteam_id, tk.cr_ownerteam_id);

UPDATE cr_nonconformity nc
JOIN cr_ticket tk ON tk.id = nc.cr_ticket_id
SET nc.cr_ownerteam_id = COALESCE(nc.cr_ownerteam_id, tk.cr_ownerteam_id);

-- -----------------------------
-- 6) Reglas de transición de estado
-- -----------------------------
CREATE TABLE IF NOT EXISTS cr_security_ticket_status_rule (
  id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  cr_role_id BIGINT UNSIGNED NOT NULL,
  cr_from_status VARCHAR(30) NOT NULL,
  cr_to_status VARCHAR(30) NOT NULL,
  cr_isallowed TINYINT(1) NOT NULL DEFAULT 1,
  cr_notes VARCHAR(255) NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  UNIQUE KEY uq_cr_sec_status_rule (cr_role_id, cr_from_status, cr_to_status),
  CONSTRAINT fk_cr_sec_status_rule_role FOREIGN KEY (cr_role_id) REFERENCES cr_security_role(id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

DELETE sr
FROM cr_security_ticket_status_rule sr
JOIN cr_security_role r ON r.id = sr.cr_role_id
WHERE r.cr_name IN ('cr_Recepcion', 'cr_Tecnico', 'cr_Logistica', 'cr_Coordinacion', 'cr_Administracion', 'cr_Calidad');

INSERT INTO cr_security_ticket_status_rule (cr_role_id, cr_from_status, cr_to_status, cr_isallowed, cr_notes)
SELECT r.id, x.cr_from_status, x.cr_to_status, 1, x.cr_notes
FROM cr_security_role r
JOIN (
  SELECT 'cr_Recepcion' AS role_name, 'Received' AS cr_from_status, 'Diagnosis' AS cr_to_status, 'Recepcion entrega a diagnostico' AS cr_notes

  UNION ALL SELECT 'cr_Tecnico', 'Diagnosis', 'WaitingParts', 'Tecnico solicita partes'
  UNION ALL SELECT 'cr_Tecnico', 'Diagnosis', 'RepairInProgress', 'Tecnico inicia reparacion'
  UNION ALL SELECT 'cr_Tecnico', 'WaitingParts', 'RepairInProgress', 'Partes disponibles'
  UNION ALL SELECT 'cr_Tecnico', 'RepairInProgress', 'Testing', 'Pasa a pruebas'
  UNION ALL SELECT 'cr_Tecnico', 'Testing', 'ReadyToShip', 'Listo para despacho'

  UNION ALL SELECT 'cr_Logistica', 'ReadyToShip', 'Shipped', 'Despacho'

  UNION ALL SELECT 'cr_Coordinacion', 'Shipped', 'Closed', 'Cierre tecnico autorizado'
  UNION ALL SELECT 'cr_Coordinacion', 'Diagnosis', 'Cancelled', 'Cancelacion autorizada'
  UNION ALL SELECT 'cr_Coordinacion', 'RepairInProgress', 'Cancelled', 'Cancelacion autorizada'

  UNION ALL SELECT 'cr_Administracion', 'Shipped', 'Closed', 'Cierre administrativo final'

  UNION ALL SELECT 'cr_Calidad', 'Testing', 'RepairInProgress', 'Rechazo por calidad/retrabajo'
) x ON x.role_name = r.cr_name;

-- -----------------------------
-- 7) View de acceso multi-sede (row-level)
-- -----------------------------
-- Uso:
-- SELECT * FROM cr_v_ticket_secure WHERE cr_useremail = 'usuario@dominio';
-- Coordinacion/Calidad/Admin ven todo; resto segun team o scope de sitio.
CREATE OR REPLACE VIEW cr_v_ticket_secure AS
SELECT DISTINCT ur.cr_useremail, t.*
FROM cr_ticket t
JOIN cr_security_user_role ur ON ur.cr_isactive = 1
JOIN cr_security_role r ON r.id = ur.cr_role_id
WHERE r.cr_isactive = 1
  AND r.cr_name IN ('cr_AdminSistema', 'cr_Coordinacion', 'cr_Calidad')
UNION
SELECT DISTINCT tm.cr_useremail, t.*
FROM cr_ticket t
JOIN cr_team_member tm ON tm.cr_team_id = t.cr_ownerteam_id
WHERE tm.cr_isactive = 1
UNION
SELECT DISTINCT us.cr_useremail, t.*
FROM cr_ticket t
JOIN cr_security_user_site_scope us
  ON us.cr_site_id IN (t.cr_sitein_id, t.cr_currentsite_id)
WHERE us.cr_can_read = 1
  AND us.cr_isactive = 1;

-- -----------------------------
-- 8) Triggers ISO: no borrado salvo AdminSistema
-- -----------------------------
-- Requiere que la app asigne variable de sesion SQL:
-- SET @app_is_admin = 1 para AdminSistema, 0 para resto.

DELIMITER $$

DROP TRIGGER IF EXISTS trg_cr_ticket_before_delete $$
CREATE TRIGGER trg_cr_ticket_before_delete
BEFORE DELETE ON cr_ticket
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_ticket';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_asset_before_delete $$
CREATE TRIGGER trg_cr_asset_before_delete
BEFORE DELETE ON cr_asset
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_asset';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_tickethistory_before_update $$
CREATE TRIGGER trg_cr_tickethistory_before_update
BEFORE UPDATE ON cr_tickethistory
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'cr_tickethistory is append-only';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_tickethistory_before_delete $$
CREATE TRIGGER trg_cr_tickethistory_before_delete
BEFORE DELETE ON cr_tickethistory
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_tickethistory';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_worklog_before_delete $$
CREATE TRIGGER trg_cr_worklog_before_delete
BEFORE DELETE ON cr_worklog
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_worklog';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_movement_before_delete $$
CREATE TRIGGER trg_cr_movement_before_delete
BEFORE DELETE ON cr_movement
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_movement';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_ticketmaterial_before_delete $$
CREATE TRIGGER trg_cr_ticketmaterial_before_delete
BEFORE DELETE ON cr_ticketmaterial
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_ticketmaterial';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_assetexchange_before_delete $$
CREATE TRIGGER trg_cr_assetexchange_before_delete
BEFORE DELETE ON cr_assetexchange
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_assetexchange';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_ticketdocument_before_delete $$
CREATE TRIGGER trg_cr_ticketdocument_before_delete
BEFORE DELETE ON cr_ticketdocument
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_ticketdocument';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_person_before_delete $$
CREATE TRIGGER trg_cr_person_before_delete
BEFORE DELETE ON cr_person
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_person';
  END IF;
END$$

DROP TRIGGER IF EXISTS trg_cr_usersettings_before_delete $$
CREATE TRIGGER trg_cr_usersettings_before_delete
BEFORE DELETE ON cr_usersettings
FOR EACH ROW
BEGIN
  IF COALESCE(@app_is_admin, 0) <> 1 THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Delete is forbidden (ISO policy): cr_usersettings';
  END IF;
END$$

-- Regla de cambio de estado por rol + cierre condicionado
-- Requiere @app_role_name = rol activo (ej: cr_Tecnico)
DROP TRIGGER IF EXISTS trg_cr_ticket_before_update_security $$
CREATE TRIGGER trg_cr_ticket_before_update_security
BEFORE UPDATE ON cr_ticket
FOR EACH ROW
BEGIN
  DECLARE v_role_id BIGINT UNSIGNED;
  DECLARE v_allowed INT DEFAULT 0;
  DECLARE v_diag_evidence INT DEFAULT 0;

  IF NEW.cr_sitein_id <> OLD.cr_sitein_id THEN
    SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'cr_sitein_id is immutable';
  END IF;

  IF NEW.cr_status <> OLD.cr_status THEN
    IF COALESCE(@app_is_admin, 0) <> 1 THEN
      SELECT id INTO v_role_id FROM cr_security_role WHERE cr_name = @app_role_name LIMIT 1;
      IF v_role_id IS NULL THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Missing @app_role_name in DB session';
      END IF;

      SELECT COUNT(1) INTO v_allowed
      FROM cr_security_ticket_status_rule sr
      WHERE sr.cr_role_id = v_role_id
        AND sr.cr_from_status = OLD.cr_status
        AND sr.cr_to_status = NEW.cr_status
        AND sr.cr_isallowed = 1;

      IF v_allowed = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Status transition not allowed for role';
      END IF;

      IF NEW.cr_status = 'Closed' THEN
        IF @app_role_name NOT IN ('cr_Coordinacion', 'cr_Administracion') THEN
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Only Coordinacion/Administracion can close tickets';
        END IF;

        IF NEW.cr_technicalclosureready <> 1 THEN
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Technical closure flag is required before Closed';
        END IF;

        IF NEW.cr_administrativeclosuredone <> 1 THEN
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Administrative closure flag is required before Closed';
        END IF;

        SELECT COUNT(1) INTO v_diag_evidence
        FROM cr_ticketdocument td
        WHERE td.cr_ticket_id = NEW.id
          AND td.cr_documenttype IN ('DiagnosticReport', 'TestEvidence');

        IF v_diag_evidence = 0 THEN
          SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'Cannot close ticket without evidence (DiagnosticReport/TestEvidence)';
        END IF;
      END IF;
    END IF;
  END IF;
END$$

DELIMITER ;
