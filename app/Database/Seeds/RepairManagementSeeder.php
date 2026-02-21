<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RepairManagementSeeder extends Seeder
{
    public function run(): void
    {
        $now = date('Y-m-d H:i:s');

        $this->db->table('rm_user')->insertBatch([
            ['username' => 'tech.es', 'full_name' => 'Tecnico ES', 'email' => 'tech.es@example.local', 'role' => 'RM_Technician', 'created_at' => $now],
            ['username' => 'coord.it', 'full_name' => 'Coordinador IT', 'email' => 'coord.it@example.local', 'role' => 'RM_Coordinator', 'created_at' => $now],
            ['username' => 'admin.global', 'full_name' => 'Admin Global', 'email' => 'admin@example.local', 'role' => 'RM_Admin', 'created_at' => $now],
        ]);

        $this->db->table('rm_site')->insertBatch([
            ['rm_sitecode' => 'ES-CAST', 'rm_name' => 'Castellon Repair Hub', 'rm_country' => 'ES', 'created_at' => $now],
            ['rm_sitecode' => 'IT-MOD', 'rm_name' => 'Modena Service Center', 'rm_country' => 'IT', 'created_at' => $now],
            ['rm_sitecode' => 'PT-XXX', 'rm_name' => 'Porto Service Center', 'rm_country' => 'PT', 'created_at' => $now],
            ['rm_sitecode' => 'FR-XXX', 'rm_name' => 'Lyon Service Center', 'rm_country' => 'FR', 'created_at' => $now],
        ]);

        $sites = $this->db->table('rm_site')->get()->getResultArray();
        $siteByCode = [];
        foreach ($sites as $site) {
            $siteByCode[$site['rm_sitecode']] = (int) $site['id'];
        }

        $users = $this->db->table('rm_user')->get()->getResultArray();
        $userByUsername = [];
        foreach ($users as $user) {
            $userByUsername[$user['username']] = (int) $user['id'];
        }

        $this->db->table('rm_employeeprofile')->insertBatch([
            ['rm_user_id' => $userByUsername['tech.es'], 'rm_homesite_id' => $siteByCode['ES-CAST'], 'rm_can_override_site' => 0, 'created_at' => $now],
            ['rm_user_id' => $userByUsername['coord.it'], 'rm_homesite_id' => $siteByCode['IT-MOD'], 'rm_can_override_site' => 1, 'created_at' => $now],
            ['rm_user_id' => $userByUsername['admin.global'], 'rm_homesite_id' => $siteByCode['ES-CAST'], 'rm_can_override_site' => 1, 'created_at' => $now],
        ]);

        if ($this->db->tableExists('rm_userpreference')) {
            $this->db->table('rm_userpreference')->insertBatch([
                ['rm_user_id' => $userByUsername['tech.es'], 'rm_default_locale' => 'es', 'rm_default_site_id' => $siteByCode['ES-CAST'], 'created_at' => $now],
                ['rm_user_id' => $userByUsername['coord.it'], 'rm_default_locale' => 'it', 'rm_default_site_id' => $siteByCode['IT-MOD'], 'created_at' => $now],
                ['rm_user_id' => $userByUsername['admin.global'], 'rm_default_locale' => 'en', 'rm_default_site_id' => $siteByCode['ES-CAST'], 'created_at' => $now],
            ]);
        }

        $this->db->table('rm_siteconfiguration')->insertBatch([
            ['rm_site_id' => $siteByCode['ES-CAST'], 'rm_defaultpriority' => 'High', 'rm_defaultticketstatus' => 'Received', 'rm_close_requires_solution' => 1, 'rm_require_testreport_on_close' => 0, 'rm_default_test_checklist' => 'Visual check; Power on; Burn-in test', 'created_at' => $now],
            ['rm_site_id' => $siteByCode['IT-MOD'], 'rm_defaultpriority' => 'Medium', 'rm_defaultticketstatus' => 'Received', 'rm_close_requires_solution' => 1, 'rm_require_testreport_on_close' => 1, 'rm_default_test_checklist' => 'Visual check; Functional test', 'created_at' => $now],
            ['rm_site_id' => $siteByCode['PT-XXX'], 'rm_defaultpriority' => 'Medium', 'rm_defaultticketstatus' => 'Diagnosis', 'rm_close_requires_solution' => 1, 'rm_require_testreport_on_close' => 0, 'rm_default_test_checklist' => 'Incoming test; Safety check', 'created_at' => $now],
            ['rm_site_id' => $siteByCode['FR-XXX'], 'rm_defaultpriority' => 'Low', 'rm_defaultticketstatus' => 'Received', 'rm_close_requires_solution' => 0, 'rm_require_testreport_on_close' => 0, 'rm_default_test_checklist' => 'Basic bench test', 'created_at' => $now],
        ]);

        $this->db->table('rm_customer')->insertBatch([
            ['rm_customercode' => 'CUST-001', 'rm_legalname' => 'Global Ceramics SA', 'rm_tradename' => 'Global Ceramics', 'created_at' => $now],
            ['rm_customercode' => 'CUST-002', 'rm_legalname' => 'Euro Motors SPA', 'rm_tradename' => 'Euro Motors', 'created_at' => $now],
        ]);

        $customers = $this->db->table('rm_customer')->get()->getResultArray();
        $customerByCode = [];
        foreach ($customers as $customer) {
            $customerByCode[$customer['rm_customercode']] = (int) $customer['id'];
        }

        $this->db->table('rm_customerplant')->insertBatch([
            ['rm_plantcode' => 'PLANT-ES-01', 'rm_customer_id' => $customerByCode['CUST-001'], 'rm_name' => 'Castellon Plant 1', 'created_at' => $now],
            ['rm_plantcode' => 'PLANT-IT-01', 'rm_customer_id' => $customerByCode['CUST-002'], 'rm_name' => 'Modena Plant A', 'created_at' => $now],
            ['rm_plantcode' => 'PLANT-FR-01', 'rm_customer_id' => $customerByCode['CUST-001'], 'rm_name' => 'Lyon Plant Central', 'created_at' => $now],
        ]);

        $this->db->table('rm_productmodel')->insertBatch([
            ['rm_modelcode' => 'MDL-CTRL-100', 'rm_description' => 'Controller 100', 'created_at' => $now],
            ['rm_modelcode' => 'MDL-PSU-200', 'rm_description' => 'Power Supply 200', 'created_at' => $now],
            ['rm_modelcode' => 'MDL-HMI-300', 'rm_description' => 'HMI Panel 300', 'created_at' => $now],
        ]);

        $this->db->table('rm_material')->insertBatch([
            ['rm_partnumber' => 'MAT-CAP-100', 'rm_description' => 'Capacitor 100uF', 'rm_unit' => 'Unit', 'created_at' => $now],
            ['rm_partnumber' => 'MAT-RES-10K', 'rm_description' => 'Resistor 10K', 'rm_unit' => 'Unit', 'created_at' => $now],
            ['rm_partnumber' => 'MAT-IC-555', 'rm_description' => 'Timer IC 555', 'rm_unit' => 'Unit', 'created_at' => $now],
            ['rm_partnumber' => 'MAT-FAN-24V', 'rm_description' => 'Cooling Fan 24V', 'rm_unit' => 'Unit', 'created_at' => $now],
            ['rm_partnumber' => 'MAT-CONN-8P', 'rm_description' => 'Connector 8 pin', 'rm_unit' => 'Unit', 'created_at' => $now],
        ]);

        $models = $this->db->table('rm_productmodel')->get()->getResultArray();
        $modelByCode = [];
        foreach ($models as $model) {
            $modelByCode[$model['rm_modelcode']] = (int) $model['id'];
        }

        $plants = $this->db->table('rm_customerplant')->get()->getResultArray();
        $plantByCode = [];
        foreach ($plants as $plant) {
            $plantByCode[$plant['rm_plantcode']] = (int) $plant['id'];
        }

        $this->db->table('rm_asset')->insertBatch([
            [
                'rm_serialnumber' => 'SN-ES-0001',
                'rm_model_id' => $modelByCode['MDL-CTRL-100'],
                'rm_status' => 'InRepair',
                'rm_currentownertype' => 'CustomerPlant',
                'rm_currentownerplant_id' => $plantByCode['PLANT-ES-01'],
                'rm_currentownersite_id' => null,
                'rm_currentlocationsite_id' => $siteByCode['ES-CAST'],
                'created_at' => $now,
            ],
            [
                'rm_serialnumber' => 'SN-IT-0009',
                'rm_model_id' => $modelByCode['MDL-HMI-300'],
                'rm_status' => 'InTransit',
                'rm_currentownertype' => 'CompanySite',
                'rm_currentownerplant_id' => null,
                'rm_currentownersite_id' => $siteByCode['IT-MOD'],
                'rm_currentlocationsite_id' => $siteByCode['IT-MOD'],
                'created_at' => $now,
            ],
        ]);

        $assets = $this->db->table('rm_asset')->get()->getResultArray();
        $assetBySerial = [];
        foreach ($assets as $asset) {
            $assetBySerial[$asset['rm_serialnumber']] = (int) $asset['id'];
        }

        $receivedDate = date('Y-m-d H:i:s', strtotime('-2 days'));

        $this->db->table('rm_repairticket')->insertBatch([
            [
                'rm_intakesite_id' => $siteByCode['ES-CAST'],
                'rm_currentprocessingsite_id' => $siteByCode['ES-CAST'],
                'rm_sapnoticenumber' => 'SAP-ES-10001',
                'rm_customerplant_id' => $plantByCode['PLANT-ES-01'],
                'rm_asset_id' => $assetBySerial['SN-ES-0001'],
                'rm_createdby_id' => $userByUsername['tech.es'],
                'rm_receivedon' => $receivedDate,
                'rm_reportedfailure' => 'No enciende al arrancar linea.',
                'rm_detectedfailure' => 'Fallo en etapa de alimentacion primaria.',
                'rm_repairsln' => null,
                'rm_status' => 'Diagnosis',
                'rm_priority' => 'High',
                'rm_assignedto_id' => $userByUsername['tech.es'],
                'rm_iswarranty' => 1,
                'rm_isnonrepairable' => 0,
                'rm_replacementgiven' => 0,
                'created_at' => $now,
            ],
            [
                'rm_intakesite_id' => $siteByCode['IT-MOD'],
                'rm_currentprocessingsite_id' => $siteByCode['FR-XXX'],
                'rm_sapnoticenumber' => 'SAP-IT-20009',
                'rm_customerplant_id' => $plantByCode['PLANT-IT-01'],
                'rm_asset_id' => $assetBySerial['SN-IT-0009'],
                'rm_createdby_id' => $userByUsername['coord.it'],
                'rm_receivedon' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'rm_reportedfailure' => 'Pantalla sin respuesta tactil.',
                'rm_detectedfailure' => 'Controlador tactil dañado.',
                'rm_repairsln' => 'En espera de repuesto.',
                'rm_status' => 'WaitingParts',
                'rm_priority' => 'Medium',
                'rm_assignedto_id' => $userByUsername['coord.it'],
                'rm_iswarranty' => 0,
                'rm_isnonrepairable' => 0,
                'rm_replacementgiven' => 0,
                'created_at' => $now,
            ],
        ]);

        $tickets = $this->db->table('rm_repairticket')->get()->getResultArray();
        $ticketBySap = [];
        foreach ($tickets as $ticket) {
            $ticketBySap[$ticket['rm_sapnoticenumber']] = (int) $ticket['id'];
        }

        $materials = $this->db->table('rm_material')->get()->getResultArray();
        $materialByPart = [];
        foreach ($materials as $material) {
            $materialByPart[$material['rm_partnumber']] = (int) $material['id'];
        }

        $this->db->table('rm_ticketstatushistory')->insertBatch([
            ['rm_ticket_id' => $ticketBySap['SAP-ES-10001'], 'rm_fromstatus' => null, 'rm_tostatus' => 'Received', 'rm_changedby_id' => $userByUsername['tech.es'], 'rm_changedat' => $receivedDate, 'rm_comment' => 'Ticket creado', 'created_at' => $now],
            ['rm_ticket_id' => $ticketBySap['SAP-ES-10001'], 'rm_fromstatus' => 'Received', 'rm_tostatus' => 'Diagnosis', 'rm_changedby_id' => $userByUsername['tech.es'], 'rm_changedat' => date('Y-m-d H:i:s', strtotime('-1 day')), 'rm_comment' => 'En analisis', 'created_at' => $now],
            ['rm_ticket_id' => $ticketBySap['SAP-IT-20009'], 'rm_fromstatus' => null, 'rm_tostatus' => 'Received', 'rm_changedby_id' => $userByUsername['coord.it'], 'rm_changedat' => date('Y-m-d H:i:s', strtotime('-1 day')), 'rm_comment' => 'Ticket creado', 'created_at' => $now],
            ['rm_ticket_id' => $ticketBySap['SAP-IT-20009'], 'rm_fromstatus' => 'Received', 'rm_tostatus' => 'WaitingParts', 'rm_changedby_id' => $userByUsername['coord.it'], 'rm_changedat' => $now, 'rm_comment' => 'Esperando repuesto', 'created_at' => $now],
        ]);

        $this->db->table('rm_ticketmaterial')->insert([
            'rm_ticket_id' => $ticketBySap['SAP-ES-10001'],
            'rm_material_id' => $materialByPart['MAT-CAP-100'],
            'rm_qty' => 2,
            'rm_usedby_id' => $userByUsername['tech.es'],
            'rm_usedat' => $now,
            'rm_comment' => 'Reemplazo de capacitores',
            'created_at' => $now,
        ]);

        $this->db->table('rm_assetmovement')->insert([
            'rm_asset_id' => $assetBySerial['SN-IT-0009'],
            'rm_fromsite_id' => $siteByCode['IT-MOD'],
            'rm_tosite_id' => $siteByCode['FR-XXX'],
            'rm_movementtype' => 'InterSiteTransfer',
            'rm_relatedticket_id' => $ticketBySap['SAP-IT-20009'],
            'rm_movedat' => $now,
            'rm_notes' => 'Transferencia para disponibilidad de repuesto.',
            'created_at' => $now,
        ]);
    }
}
