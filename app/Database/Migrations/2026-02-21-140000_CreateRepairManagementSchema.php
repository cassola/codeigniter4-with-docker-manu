<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateRepairManagementSchema extends Migration
{
    public function up(): void
    {
        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'username' => ['type' => 'VARCHAR', 'constraint' => 80],
            'full_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'email' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'role' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'RM_Technician'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('username', 'uk_rm_user_username');
        $this->forge->createTable('rm_user', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_sitecode' => ['type' => 'VARCHAR', 'constraint' => 20],
            'rm_name' => ['type' => 'VARCHAR', 'constraint' => 120],
            'rm_country' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_sitecode', 'uk_rm_site_sitecode');
        $this->forge->createTable('rm_site', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_customercode' => ['type' => 'VARCHAR', 'constraint' => 40],
            'rm_legalname' => ['type' => 'VARCHAR', 'constraint' => 150],
            'rm_tradename' => ['type' => 'VARCHAR', 'constraint' => 150, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_customercode', 'uk_rm_customer_code');
        $this->forge->createTable('rm_customer', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_plantcode' => ['type' => 'VARCHAR', 'constraint' => 40],
            'rm_customer_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_name' => ['type' => 'VARCHAR', 'constraint' => 150],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_plantcode', 'uk_rm_customerplant_code');
        $this->forge->addForeignKey('rm_customer_id', 'rm_customer', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('rm_customerplant', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_modelcode' => ['type' => 'VARCHAR', 'constraint' => 40],
            'rm_description' => ['type' => 'VARCHAR', 'constraint' => 180],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_modelcode', 'uk_rm_productmodel_code');
        $this->forge->createTable('rm_productmodel', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_partnumber' => ['type' => 'VARCHAR', 'constraint' => 40],
            'rm_description' => ['type' => 'VARCHAR', 'constraint' => 180],
            'rm_unit' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Unit'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_partnumber', 'uk_rm_material_partnumber');
        $this->forge->createTable('rm_material', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_serialnumber' => ['type' => 'VARCHAR', 'constraint' => 80],
            'rm_model_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_status' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'InCustomer'],
            'rm_currentownertype' => ['type' => 'VARCHAR', 'constraint' => 30, 'default' => 'CustomerPlant'],
            'rm_currentownerplant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_currentownersite_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_currentlocationsite_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_serialnumber', 'uk_rm_asset_serialnumber');
        $this->forge->addForeignKey('rm_model_id', 'rm_productmodel', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('rm_currentownerplant_id', 'rm_customerplant', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('rm_currentownersite_id', 'rm_site', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('rm_currentlocationsite_id', 'rm_site', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('rm_asset', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_intakesite_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_currentprocessingsite_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_sapnoticenumber' => ['type' => 'VARCHAR', 'constraint' => 50],
            'rm_customerplant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_asset_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_createdby_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_receivedon' => ['type' => 'DATETIME'],
            'rm_reportedfailure' => ['type' => 'TEXT'],
            'rm_detectedfailure' => ['type' => 'TEXT', 'null' => true],
            'rm_repairsln' => ['type' => 'TEXT', 'null' => true],
            'rm_status' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'Received'],
            'rm_priority' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Medium'],
            'rm_assignedto_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_closedon' => ['type' => 'DATETIME', 'null' => true],
            'rm_iswarranty' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'rm_isnonrepairable' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'rm_replacementgiven' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'rm_replacementasset_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_closurecode' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey(['rm_intakesite_id', 'rm_sapnoticenumber'], 'uk_rm_repairticket_sap_per_site');
        $this->forge->addForeignKey('rm_intakesite_id', 'rm_site', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('rm_currentprocessingsite_id', 'rm_site', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('rm_customerplant_id', 'rm_customerplant', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('rm_asset_id', 'rm_asset', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('rm_createdby_id', 'rm_user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('rm_assignedto_id', 'rm_user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('rm_replacementasset_id', 'rm_asset', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('rm_repairticket', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_ticket_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_fromstatus' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'rm_tostatus' => ['type' => 'VARCHAR', 'constraint' => 40],
            'rm_changedby_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_changedat' => ['type' => 'DATETIME'],
            'rm_comment' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('rm_ticket_id', 'rm_repairticket', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rm_changedby_id', 'rm_user', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('rm_ticketstatushistory', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_ticket_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_material_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_qty' => ['type' => 'DECIMAL', 'constraint' => '10,2'],
            'rm_usedby_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_usedat' => ['type' => 'DATETIME'],
            'rm_comment' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('rm_ticket_id', 'rm_repairticket', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rm_material_id', 'rm_material', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('rm_usedby_id', 'rm_user', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('rm_ticketmaterial', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_site_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_defaultpriority' => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'Medium'],
            'rm_defaultticketstatus' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'Received'],
            'rm_close_requires_solution' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 1],
            'rm_require_testreport_on_close' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'rm_default_test_checklist' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_site_id', 'uk_rm_siteconfiguration_site');
        $this->forge->addForeignKey('rm_site_id', 'rm_site', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('rm_siteconfiguration', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_user_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_homesite_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_can_override_site' => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addUniqueKey('rm_user_id', 'uk_rm_employeeprofile_user');
        $this->forge->addForeignKey('rm_user_id', 'rm_user', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rm_homesite_id', 'rm_site', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('rm_employeeprofile', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_asset_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_fromsite_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_tosite_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_movementtype' => ['type' => 'VARCHAR', 'constraint' => 40, 'default' => 'InterSiteTransfer'],
            'rm_relatedticket_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_movedat' => ['type' => 'DATETIME'],
            'rm_notes' => ['type' => 'TEXT', 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('rm_asset_id', 'rm_asset', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rm_fromsite_id', 'rm_site', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('rm_tosite_id', 'rm_site', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->addForeignKey('rm_relatedticket_id', 'rm_repairticket', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('rm_assetmovement', true);

        $this->forge->addField([
            'id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'rm_asset_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true],
            'rm_ownertype' => ['type' => 'VARCHAR', 'constraint' => 30],
            'rm_ownerplant_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_ownersite_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'rm_startdate' => ['type' => 'DATETIME'],
            'rm_enddate' => ['type' => 'DATETIME', 'null' => true],
            'rm_reason' => ['type' => 'VARCHAR', 'constraint' => 40, 'null' => true],
            'rm_relatedticket_id' => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => true],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('rm_asset_id', 'rm_asset', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('rm_ownerplant_id', 'rm_customerplant', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('rm_ownersite_id', 'rm_site', 'id', 'SET NULL', 'CASCADE');
        $this->forge->addForeignKey('rm_relatedticket_id', 'rm_repairticket', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('rm_assetownership', true);
    }

    public function down(): void
    {
        $tables = [
            'rm_assetownership',
            'rm_assetmovement',
            'rm_employeeprofile',
            'rm_siteconfiguration',
            'rm_ticketmaterial',
            'rm_ticketstatushistory',
            'rm_repairticket',
            'rm_asset',
            'rm_material',
            'rm_productmodel',
            'rm_customerplant',
            'rm_customer',
            'rm_site',
            'rm_user',
        ];

        foreach ($tables as $table) {
            $this->forge->dropTable($table, true);
        }
    }
}
