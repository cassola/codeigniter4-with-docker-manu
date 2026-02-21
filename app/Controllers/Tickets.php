<?php

namespace App\Controllers;

use CodeIgniter\Exceptions\PageNotFoundException;

class Tickets extends BaseController
{
    private array $statusChoices = [
        'Received',
        'Diagnosis',
        'WaitingParts',
        'RepairInProgress',
        'Testing',
        'ReadyToShip',
        'Shipped',
        'Closed',
        'Cancelled',
    ];

    private array $priorityChoices = ['Low', 'Medium', 'High', 'Critical'];

    public function index()
    {
        $db = db_connect();
        $statusFilter = (string) $this->request->getGet('status');
        $siteFilter = (int) $this->request->getGet('site_id');
        if ($siteFilter <= 0) {
            $siteFilter = (int) (session('default_site_id') ?? 0);
        }

        $query = $db->table('rm_repairticket t')
            ->select('t.*, si.rm_sitecode AS intake_site, sp.rm_sitecode AS processing_site, a.rm_serialnumber, cp.rm_name AS customer_plant')
            ->join('rm_site si', 'si.id = t.rm_intakesite_id')
            ->join('rm_site sp', 'sp.id = t.rm_currentprocessingsite_id')
            ->join('rm_asset a', 'a.id = t.rm_asset_id')
            ->join('rm_customerplant cp', 'cp.id = t.rm_customerplant_id')
            ->orderBy('t.created_at', 'DESC');

        if ($statusFilter !== '') {
            $query->where('t.rm_status', $statusFilter);
        }
        if ($siteFilter > 0) {
            $query->where('sp.id', $siteFilter);
        }

        $tickets = $query->get()->getResultArray();
        $sites = $db->table('rm_site')->orderBy('rm_sitecode')->get()->getResultArray();

        return view('layouts/powerapp', [
            'title' => t('tickets'),
            'area' => t('tickets'),
            'commandActions' => [
                ['label' => t('new_ticket'), 'href' => site_url('tickets/new')],
                ['label' => t('dashboard'), 'href' => site_url('dashboard')],
            ],
            'content' => view('tickets/index', [
                'tickets' => $tickets,
                'sites' => $sites,
                'statusChoices' => $this->statusChoices,
                'statusFilter' => $statusFilter,
                'siteFilter' => $siteFilter,
            ]),
        ]);
    }

    public function new()
    {
        return $this->ticketForm();
    }

    public function create()
    {
        $db = db_connect();
        $data = $this->request->getPost();

        $creatorId = (int) ($data['rm_createdby_id'] ?? 0);
        $intakeSiteId = (int) ($data['rm_intakesite_id'] ?? 0);

        if ($intakeSiteId === 0) {
            $intakeSiteId = (int) (session('default_site_id') ?? 0);
        }

        if ($intakeSiteId === 0 && $creatorId > 0) {
            $profile = $db->table('rm_employeeprofile')->where('rm_user_id', $creatorId)->get()->getRowArray();
            if ($profile) {
                $intakeSiteId = (int) $profile['rm_homesite_id'];
            }
        }

        if ($intakeSiteId === 0) {
            return redirect()->back()->withInput()->with('error', t('error_intake_required'));
        }

        $sapNotice = trim((string) ($data['rm_sapnoticenumber'] ?? ''));
        if ($sapNotice === '') {
            return redirect()->back()->withInput()->with('error', t('error_sap_required'));
        }

        $exists = $db->table('rm_repairticket')
            ->where('rm_intakesite_id', $intakeSiteId)
            ->where('rm_sapnoticenumber', $sapNotice)
            ->countAllResults();

        if ($exists > 0) {
            return redirect()->back()->withInput()->with('error', t('error_duplicate_ticket'));
        }

        $siteConfig = $db->table('rm_siteconfiguration')->where('rm_site_id', $intakeSiteId)->get()->getRowArray();
        $status = trim((string) ($data['rm_status'] ?? ''));
        $priority = trim((string) ($data['rm_priority'] ?? ''));

        if ($status === '' && $siteConfig) {
            $status = (string) $siteConfig['rm_defaultticketstatus'];
        }
        if ($priority === '' && $siteConfig) {
            $priority = (string) $siteConfig['rm_defaultpriority'];
        }
        if ($status === '') {
            $status = 'Received';
        }
        if ($priority === '') {
            $priority = 'Medium';
        }

        $insert = [
            'rm_intakesite_id' => $intakeSiteId,
            'rm_currentprocessingsite_id' => (int) ($data['rm_currentprocessingsite_id'] ?: $intakeSiteId),
            'rm_sapnoticenumber' => $sapNotice,
            'rm_customerplant_id' => (int) ($data['rm_customerplant_id'] ?? 0),
            'rm_asset_id' => (int) ($data['rm_asset_id'] ?? 0),
            'rm_createdby_id' => $creatorId ?: null,
            'rm_receivedon' => $data['rm_receivedon'] ?: date('Y-m-d H:i:s'),
            'rm_reportedfailure' => trim((string) ($data['rm_reportedfailure'] ?? '')),
            'rm_detectedfailure' => trim((string) ($data['rm_detectedfailure'] ?? '')) ?: null,
            'rm_repairsln' => trim((string) ($data['rm_repairsln'] ?? '')) ?: null,
            'rm_status' => $status,
            'rm_priority' => $priority,
            'rm_assignedto_id' => ((int) ($data['rm_assignedto_id'] ?? 0)) ?: null,
            'rm_iswarranty' => $this->boolField('rm_iswarranty'),
            'rm_isnonrepairable' => $this->boolField('rm_isnonrepairable'),
            'rm_replacementgiven' => $this->boolField('rm_replacementgiven'),
            'rm_replacementasset_id' => ((int) ($data['rm_replacementasset_id'] ?? 0)) ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ];

        if ($insert['rm_replacementgiven'] && empty($insert['rm_replacementasset_id'])) {
            return redirect()->back()->withInput()->with('error', t('error_replacement_asset_required'));
        }

        if ($insert['rm_customerplant_id'] === 0 || $insert['rm_asset_id'] === 0 || $insert['rm_reportedfailure'] === '') {
            return redirect()->back()->withInput()->with('error', t('error_required_ticket_fields'));
        }

        $db->table('rm_repairticket')->insert($insert);
        $ticketId = (int) $db->insertID();

        $db->table('rm_ticketstatushistory')->insert([
            'rm_ticket_id' => $ticketId,
            'rm_fromstatus' => null,
            'rm_tostatus' => $status,
            'rm_changedby_id' => $creatorId ?: 1,
            'rm_changedat' => date('Y-m-d H:i:s'),
            'rm_comment' => t('msg_ticket_created_history'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('tickets/' . $ticketId))->with('success', t('msg_ticket_created'));
    }

    public function show(int $id)
    {
        $db = db_connect();

        $ticket = $db->table('rm_repairticket t')
            ->select('t.*, si.rm_sitecode AS intake_site, sp.rm_sitecode AS processing_site, cp.rm_name AS customer_plant, a.rm_serialnumber, a.rm_status AS asset_status')
            ->join('rm_site si', 'si.id = t.rm_intakesite_id')
            ->join('rm_site sp', 'sp.id = t.rm_currentprocessingsite_id')
            ->join('rm_customerplant cp', 'cp.id = t.rm_customerplant_id')
            ->join('rm_asset a', 'a.id = t.rm_asset_id')
            ->where('t.id', $id)
            ->get()
            ->getRowArray();

        if (! $ticket) {
            throw PageNotFoundException::forPageNotFound(t('error_ticket_not_found'));
        }

        $materials = $db->table('rm_ticketmaterial tm')
            ->select('tm.*, m.rm_partnumber, m.rm_description, u.full_name AS used_by')
            ->join('rm_material m', 'm.id = tm.rm_material_id')
            ->join('rm_user u', 'u.id = tm.rm_usedby_id', 'left')
            ->where('tm.rm_ticket_id', $id)
            ->orderBy('tm.rm_usedat', 'DESC')
            ->get()
            ->getResultArray();

        $history = $db->table('rm_ticketstatushistory h')
            ->select('h.*, u.full_name AS changed_by')
            ->join('rm_user u', 'u.id = h.rm_changedby_id', 'left')
            ->where('h.rm_ticket_id', $id)
            ->orderBy('h.rm_changedat', 'DESC')
            ->get()
            ->getResultArray();

        $movements = $db->table('rm_assetmovement mv')
            ->select('mv.*, fs.rm_sitecode AS from_site, ts.rm_sitecode AS to_site')
            ->join('rm_site fs', 'fs.id = mv.rm_fromsite_id')
            ->join('rm_site ts', 'ts.id = mv.rm_tosite_id')
            ->where('mv.rm_relatedticket_id', $id)
            ->orderBy('mv.rm_movedat', 'DESC')
            ->get()
            ->getResultArray();

        return view('layouts/powerapp', [
            'title' => t('ticket') . ' ' . $ticket['rm_sapnoticenumber'],
            'area' => t('tickets'),
            'commandActions' => [
                ['label' => t('transfer_to_site'), 'href' => '#transfer-form'],
                ['label' => t('add_material'), 'href' => '#material-form'],
                ['label' => t('mark_shipped'), 'href' => '#status-form'],
                ['label' => t('close_ticket'), 'href' => '#status-form'],
            ],
            'content' => view('tickets/show', [
                'ticket' => $ticket,
                'materials' => $materials,
                'history' => $history,
                'movements' => $movements,
                'statusChoices' => $this->statusChoices,
                'sites' => db_connect()->table('rm_site')->orderBy('rm_sitecode')->get()->getResultArray(),
                'materialOptions' => db_connect()->table('rm_material')->orderBy('rm_partnumber')->get()->getResultArray(),
                'users' => db_connect()->table('rm_user')->orderBy('full_name')->get()->getResultArray(),
            ]),
        ]);
    }

    public function updateStatus(int $id)
    {
        $db = db_connect();
        $ticket = $db->table('rm_repairticket')->where('id', $id)->get()->getRowArray();
        if (! $ticket) {
            throw PageNotFoundException::forPageNotFound(t('error_ticket_not_found'));
        }

        $toStatus = trim((string) $this->request->getPost('rm_status'));
        $comment = trim((string) $this->request->getPost('rm_comment'));
        $changedBy = (int) ($this->request->getPost('rm_changedby_id') ?: 1);
        $repairSln = trim((string) $this->request->getPost('rm_repairsln'));

        if (! in_array($toStatus, $this->statusChoices, true)) {
            return redirect()->back()->with('error', t('error_invalid_status'));
        }

        if ($repairSln !== '') {
            $ticket['rm_repairsln'] = $repairSln;
        }

        if ($toStatus === 'Closed') {
            $siteConfig = $db->table('rm_siteconfiguration')
                ->where('rm_site_id', $ticket['rm_currentprocessingsite_id'])
                ->get()
                ->getRowArray();

            $requiresSln = $siteConfig ? (int) $siteConfig['rm_close_requires_solution'] === 1 : true;
            if ($requiresSln && empty($ticket['rm_repairsln']) && $repairSln === '') {
                return redirect()->back()->with('error', t('error_close_requires_solution'));
            }

            $ticket['rm_closedon'] = date('Y-m-d H:i:s');
        }

        $db->table('rm_repairticket')->where('id', $id)->update([
            'rm_status' => $toStatus,
            'rm_repairsln' => $ticket['rm_repairsln'] ?? null,
            'rm_closedon' => $ticket['rm_closedon'] ?? null,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $db->table('rm_ticketstatushistory')->insert([
            'rm_ticket_id' => $id,
            'rm_fromstatus' => $ticket['rm_status'],
            'rm_tostatus' => $toStatus,
            'rm_changedby_id' => $changedBy,
            'rm_changedat' => date('Y-m-d H:i:s'),
            'rm_comment' => $comment ?: t('msg_manual_status_change'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('tickets/' . $id))->with('success', t('msg_status_updated'));
    }

    public function transfer(int $id)
    {
        $db = db_connect();
        $ticket = $db->table('rm_repairticket')->where('id', $id)->get()->getRowArray();
        if (! $ticket) {
            throw PageNotFoundException::forPageNotFound(t('error_ticket_not_found'));
        }

        $toSite = (int) $this->request->getPost('to_site_id');
        $userId = (int) ($this->request->getPost('rm_changedby_id') ?: 1);
        $notes = trim((string) $this->request->getPost('rm_comment'));

        if ($toSite <= 0 || $toSite === (int) $ticket['rm_currentprocessingsite_id']) {
            return redirect()->back()->with('error', t('error_invalid_destination_site'));
        }

        $db->table('rm_repairticket')->where('id', $id)->update([
            'rm_currentprocessingsite_id' => $toSite,
            'rm_status' => 'RepairInProgress',
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $db->table('rm_asset')->where('id', $ticket['rm_asset_id'])->update([
            'rm_currentlocationsite_id' => $toSite,
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $db->table('rm_assetmovement')->insert([
            'rm_asset_id' => $ticket['rm_asset_id'],
            'rm_fromsite_id' => $ticket['rm_currentprocessingsite_id'],
            'rm_tosite_id' => $toSite,
            'rm_movementtype' => 'InterSiteTransfer',
            'rm_relatedticket_id' => $id,
            'rm_movedat' => date('Y-m-d H:i:s'),
            'rm_notes' => $notes ?: t('msg_transfer_action'),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        $db->table('rm_ticketstatushistory')->insert([
            'rm_ticket_id' => $id,
            'rm_fromstatus' => $ticket['rm_status'],
            'rm_tostatus' => 'RepairInProgress',
            'rm_changedby_id' => $userId,
            'rm_changedat' => date('Y-m-d H:i:s'),
            'rm_comment' => t('msg_transfer_history', [$ticket['rm_currentprocessingsite_id'], $toSite]),
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('tickets/' . $id))->with('success', t('msg_ticket_transferred'));
    }

    public function addMaterial(int $id)
    {
        $db = db_connect();
        $ticket = $db->table('rm_repairticket')->where('id', $id)->get()->getRowArray();
        if (! $ticket) {
            throw PageNotFoundException::forPageNotFound(t('error_ticket_not_found'));
        }

        $materialId = (int) $this->request->getPost('rm_material_id');
        $qty = (float) $this->request->getPost('rm_qty');
        $usedBy = (int) ($this->request->getPost('rm_usedby_id') ?: 1);
        $comment = trim((string) $this->request->getPost('rm_comment'));

        if ($materialId <= 0 || $qty <= 0) {
            return redirect()->back()->with('error', t('error_material_qty_required'));
        }

        $db->table('rm_ticketmaterial')->insert([
            'rm_ticket_id' => $id,
            'rm_material_id' => $materialId,
            'rm_qty' => $qty,
            'rm_usedby_id' => $usedBy,
            'rm_usedat' => date('Y-m-d H:i:s'),
            'rm_comment' => $comment ?: null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        return redirect()->to(site_url('tickets/' . $id))->with('success', t('msg_material_added'));
    }

    private function ticketForm()
    {
        $db = db_connect();

        return view('layouts/powerapp', [
            'title' => t('new_ticket'),
            'area' => t('tickets'),
            'commandActions' => [
                ['label' => t('save'), 'href' => '#ticket-form'],
                ['label' => t('back_to_list'), 'href' => site_url('tickets')],
            ],
            'content' => view('tickets/form', [
                'sites' => $db->table('rm_site')->orderBy('rm_sitecode')->get()->getResultArray(),
                'plants' => $db->table('rm_customerplant')->orderBy('rm_name')->get()->getResultArray(),
                'assets' => $db->table('rm_asset')->orderBy('rm_serialnumber')->get()->getResultArray(),
                'users' => $db->table('rm_user')->orderBy('full_name')->get()->getResultArray(),
                'statusChoices' => $this->statusChoices,
                'priorityChoices' => $this->priorityChoices,
            ]),
        ]);
    }

    private function boolField(string $key): int
    {
        return $this->request->getPost($key) ? 1 : 0;
    }
}
