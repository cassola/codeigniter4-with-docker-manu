<?php

namespace App\Controllers;

use CodeIgniter\Database\BaseBuilder;
use CodeIgniter\Database\Exceptions\DatabaseException;
use CodeIgniter\I18n\Time;

class RepairApp extends BaseController
{
    public function dashboard(): string
    {
        $ctx = $this->bootContext();

        $kpis = [
            'openTickets' => 0,
            'riskSla' => 0,
            'outSla' => 0,
            'inSla' => 0,
            'sitesWithOpenTickets' => 0,
        ];

        try {
            $builder = $this->db->table('cr_ticket');
            $this->applyTicketScope($builder, $ctx);
            $kpis['openTickets'] = (int) $builder->whereNotIn('cr_status', ['Closed', 'Cancelled'])->countAllResults();

            $builder = $this->db->table('cr_ticket');
            $this->applyTicketScope($builder, $ctx);
            $kpis['riskSla'] = (int) $builder
                ->whereNotIn('cr_status', ['Closed', 'Cancelled'])
                ->where('cr_sla_duedate IS NOT NULL')
                ->where('cr_sla_duedate <=', Time::now()->addHours(24)->toDateTimeString())
                ->where('cr_sla_duedate >', Time::now()->toDateTimeString())
                ->countAllResults();

            $builder = $this->db->table('cr_ticket');
            $this->applyTicketScope($builder, $ctx);
            $kpis['outSla'] = (int) $builder
                ->whereNotIn('cr_status', ['Closed', 'Cancelled'])
                ->where('cr_sla_duedate IS NOT NULL')
                ->where('cr_sla_duedate <', Time::now()->toDateTimeString())
                ->countAllResults();

            $builder = $this->db->table('cr_ticket');
            $this->applyTicketScope($builder, $ctx);
            $kpis['inSla'] = (int) $builder
                ->whereNotIn('cr_status', ['Closed', 'Cancelled'])
                ->where('cr_sla_duedate IS NOT NULL')
                ->where('cr_sla_duedate >', Time::now()->addHours(24)->toDateTimeString())
                ->countAllResults();

            $builder = $this->db->table('cr_ticket');
            $this->applyTicketScope($builder, $ctx);
            $kpis['sitesWithOpenTickets'] = (int) $builder
                ->select('COUNT(DISTINCT cr_currentsite_id) AS total', false)
                ->whereNotIn('cr_status', ['Closed', 'Cancelled'])
                ->get()->getRow('total');
        } catch (DatabaseException) {
        }

        $statusCards = [];
        try {
            $builder = $this->db->table('cr_ticket');
            $this->applyTicketScope($builder, $ctx);
            $statusCards = $builder
                ->select('cr_status, COUNT(*) AS total')
                ->groupBy('cr_status')
                ->orderBy('total', 'DESC')
                ->get()
                ->getResultArray();
        } catch (DatabaseException) {
        }

        return $this->renderScreen('dashboard', [
            'ctx' => $ctx,
            'kpis' => $kpis,
            'statusCards' => $statusCards,
        ]);
    }

    public function ticketsList(): string
    {
        $ctx = $this->bootContext();

        $filters = [
            'status' => (string) $this->request->getGet('status'),
            'priority' => (string) $this->request->getGet('priority'),
            'site' => (string) $this->request->getGet('site'),
            'q' => trim((string) $this->request->getGet('q')),
        ];

        $tickets = [];
        try {
            $builder = $this->db->table('cr_ticket t')
                ->select('t.id, t.cr_ticketnumber, t.cr_sapnotice, t.cr_status, t.cr_priority, t.cr_receiveddate, t.cr_sla_duedate, s.cr_sitecode, a.cr_serialnumber')
                ->join('cr_site s', 's.id = t.cr_currentsite_id', 'left')
                ->join('cr_asset a', 'a.id = t.cr_asset_id', 'left')
                ->orderBy('t.id', 'DESC')
                ->limit(100);

            $this->applyTicketScope($builder, $ctx, 't');

            if ($filters['status'] !== '') {
                $builder->where('t.cr_status', $filters['status']);
            }
            if ($filters['priority'] !== '') {
                $builder->where('t.cr_priority', $filters['priority']);
            }
            if ($filters['site'] !== '') {
                $builder->where('t.cr_currentsite_id', $filters['site']);
            }
            if ($filters['q'] !== '') {
                $builder->groupStart()
                    ->like('t.cr_ticketnumber', $filters['q'])
                    ->orLike('t.cr_sapnotice', $filters['q'])
                    ->orLike('a.cr_serialnumber', $filters['q'])
                    ->groupEnd();
            }

            $tickets = $builder->get()->getResultArray();
            $now = Time::now()->toDateTimeString();
            $risk = Time::now()->addHours(24)->toDateTimeString();
            foreach ($tickets as &$ticket) {
                $due = (string) ($ticket['cr_sla_duedate'] ?? '');
                if ($due === '') {
                    $ticket['cr_sla_status'] = 'n/a';
                } elseif ($due < $now) {
                    $ticket['cr_sla_status'] = 'out';
                } elseif ($due <= $risk) {
                    $ticket['cr_sla_status'] = 'risk';
                } else {
                    $ticket['cr_sla_status'] = 'in';
                }
            }
            unset($ticket);
        } catch (DatabaseException) {
        }

        return $this->renderScreen('tickets_list', [
            'ctx' => $ctx,
            'filters' => $filters,
            'tickets' => $tickets,
            'sites' => $this->getSites(),
        ]);
    }

    public function ticketDetail(int $id): string
    {
        $ctx = $this->bootContext();
        $ticket = null;
        $timeline = [];
        $message = (string) $this->request->getGet('message');
        $error = (string) $this->request->getGet('error');

        try {
            $builder = $this->db->table('cr_ticket t')
                ->select('t.*, s.cr_sitecode, ap.cr_name AS accountplant_name, a.cr_serialnumber, a.cr_model')
                ->join('cr_site s', 's.id = t.cr_currentsite_id', 'left')
                ->join('cr_accountplant ap', 'ap.id = t.cr_accountplant_id', 'left')
                ->join('cr_asset a', 'a.id = t.cr_asset_id', 'left')
                ->where('t.id', $id)
                ->limit(1);
            $this->applyTicketScope($builder, $ctx, 't');
            $ticket = $builder->get()->getRowArray();

            if ($ticket) {
                $timeline = $this->buildTimeline($id);
            }
        } catch (DatabaseException) {
        }

        return $this->renderScreen('ticket_detail', [
            'ctx' => $ctx,
            'ticket' => $ticket,
            'timeline' => $timeline,
            'message' => $message,
            'error' => $error,
            'sites' => $this->getSites(),
            'tickets' => $this->getTicketsSimple(),
            'accountPlants' => $this->getAccountPlants(),
            'assets' => $this->getAssets(),
            'statusOptions' => ['Received', 'Diagnosis', 'WaitingParts', 'RepairInProgress', 'Testing', 'ReadyToShip', 'Shipped', 'Closed', 'Cancelled'],
        ]);
    }

    public function changeStatus(int $id)
    {
        $ctx = $this->bootContext();
        $ticket = $this->getTicketForAction($id, $ctx);
        if (! $ticket) {
            return $this->redirectTicket($id, null, 'Ticket not found or out of scope');
        }

        $newStatus = trim((string) $this->request->getPost('new_status'));
        $comment = trim((string) $this->request->getPost('comment'));
        $patch = [
            'cr_repairsolution' => trim((string) $this->request->getPost('cr_repairsolution')),
            'cr_cancelreason' => trim((string) $this->request->getPost('cr_cancelreason')),
            'cr_nonrepairable' => (int) ($this->request->getPost('cr_nonrepairable') ?? $ticket['cr_nonrepairable']),
            'cr_technicalclosureready' => (int) ($this->request->getPost('cr_technicalclosureready') ?? $ticket['cr_technicalclosureready']),
            'cr_administrativeclosuredone' => (int) ($this->request->getPost('cr_administrativeclosuredone') ?? $ticket['cr_administrativeclosuredone']),
        ];

        if ($newStatus === '') {
            return $this->redirectTicket($id, null, 'newStatus is required');
        }

        try {
            $this->validateStatusTransition($ticket, $newStatus, $ctx);
            $this->validateStatusBusinessRules($ticket, $newStatus, $comment, $patch, $ctx);

            $update = [
                'cr_status' => $newStatus,
                'cr_repairsolution' => $patch['cr_repairsolution'] !== '' ? $patch['cr_repairsolution'] : $ticket['cr_repairsolution'],
                'cr_cancelreason' => $patch['cr_cancelreason'] !== '' ? $patch['cr_cancelreason'] : $ticket['cr_cancelreason'],
                'cr_nonrepairable' => $patch['cr_nonrepairable'],
                'cr_technicalclosureready' => $patch['cr_technicalclosureready'],
                'cr_administrativeclosuredone' => $patch['cr_administrativeclosuredone'],
            ];
            if ((int) $patch['cr_nonrepairable'] === 1) {
                $update['cr_outcome'] = 'NonRepairable';
            }

            if ($newStatus === 'Closed') {
                $now = Time::now()->toDateTimeString();
                $update['cr_closeddate'] = $now;
                $update['cr_technicalclosuredate'] = $update['cr_technicalclosureready'] ? $now : $ticket['cr_technicalclosuredate'];
                $update['cr_technicalclosureby'] = $ctx['userEmail'];
                $update['cr_administrativeclosuredate'] = $update['cr_administrativeclosuredone'] ? $now : $ticket['cr_administrativeclosuredate'];
                $update['cr_administrativeclosureby'] = $ctx['userEmail'];
            }

            $this->db->transStart();

            $this->db->table('cr_ticket')->where('id', $ticket['id'])->update($update);

            $this->db->table('cr_tickethistory')->insert([
                'cr_ticket_id' => (int) $ticket['id'],
                'cr_fromstatus' => (string) $ticket['cr_status'],
                'cr_tostatus' => $newStatus,
                'cr_siteattime_id' => (int) $ticket['cr_currentsite_id'],
                'cr_changedby' => $ctx['userEmail'],
                'cr_changedon' => Time::now()->toDateTimeString(),
                'cr_comment' => $comment !== '' ? $comment : ('Status change to ' . $newStatus),
                'cr_ownerteam_id' => $ticket['cr_ownerteam_id'] ?? null,
            ]);

            $this->db->transComplete();
            if (! $this->db->transStatus()) {
                throw new \RuntimeException('Failed to change status');
            }
        } catch (\Throwable $e) {
            return $this->redirectTicket($id, null, $e->getMessage());
        }

        return $this->redirectTicket($id, 'Status changed to ' . $newStatus, null);
    }

    public function transferSite(int $id)
    {
        $ctx = $this->bootContext();
        $ticket = $this->getTicketForAction($id, $ctx);
        if (! $ticket) {
            return $this->redirectTicket($id, null, 'Ticket not found or out of scope');
        }

        $toSite = (int) $this->request->getPost('to_site_id');
        $reason = trim((string) $this->request->getPost('reason'));
        if ($toSite <= 0 || $reason === '') {
            return $this->redirectTicket($id, null, 'toSite and reason are required');
        }
        if ($toSite === (int) $ticket['cr_currentsite_id']) {
            return $this->redirectTicket($id, null, 'Destination site must be different');
        }

        try {
            $this->db->transStart();

            $this->db->table('cr_movement')->insert([
                'cr_ticket_id' => (int) $ticket['id'],
                'cr_asset_id' => (int) $ticket['cr_asset_id'],
                'cr_movementtype' => 'TransferOut',
                'cr_fromsite_id' => (int) $ticket['cr_currentsite_id'],
                'cr_tosite_id' => $toSite,
                'cr_datetime' => Time::now()->toDateTimeString(),
                'cr_reference' => 'TR-' . date('YmdHis'),
                'cr_executedby' => $ctx['userEmail'],
                'cr_notes' => $reason,
                'cr_ownerteam_id' => $ticket['cr_ownerteam_id'] ?? null,
            ]);

            $this->db->table('cr_ticket')->where('id', $ticket['id'])->update([
                'cr_currentsite_id' => $toSite,
                'cr_returnrequired' => 1,
                'cr_returnstatus' => 'Planned',
            ]);

            $this->db->table('cr_asset')->where('id', $ticket['cr_asset_id'])->update([
                'cr_currentsite_id' => $toSite,
            ]);

            $this->db->table('cr_tickethistory')->insert([
                'cr_ticket_id' => (int) $ticket['id'],
                'cr_fromstatus' => (string) $ticket['cr_status'],
                'cr_tostatus' => (string) $ticket['cr_status'],
                'cr_siteattime_id' => (int) $toSite,
                'cr_changedby' => $ctx['userEmail'],
                'cr_changedon' => Time::now()->toDateTimeString(),
                'cr_comment' => 'Transfer to site ' . $toSite . ': ' . $reason,
                'cr_ownerteam_id' => $ticket['cr_ownerteam_id'] ?? null,
            ]);

            $this->db->transComplete();
            if (! $this->db->transStatus()) {
                throw new \RuntimeException('Transfer failed');
            }
        } catch (\Throwable $e) {
            return $this->redirectTicket($id, null, $e->getMessage());
        }

        return $this->redirectTicket($id, 'Transfer completed', null);
    }

    public function registerReturnOut(int $id)
    {
        $ctx = $this->bootContext();
        $ticket = $this->getTicketForAction($id, $ctx);
        if (! $ticket) {
            return $this->redirectTicket($id, null, 'Ticket not found or out of scope');
        }

        $notes = trim((string) $this->request->getPost('notes'));
        if ((int) $ticket['cr_returnrequired'] !== 1) {
            return $this->redirectTicket($id, null, 'Return is not required for this ticket');
        }

        try {
            $this->db->transStart();
            $this->db->table('cr_movement')->insert([
                'cr_ticket_id' => (int) $ticket['id'],
                'cr_asset_id' => (int) $ticket['cr_asset_id'],
                'cr_movementtype' => 'ReturnOut',
                'cr_fromsite_id' => (int) $ticket['cr_currentsite_id'],
                'cr_tosite_id' => (int) $ticket['cr_sitein_id'],
                'cr_datetime' => Time::now()->toDateTimeString(),
                'cr_reference' => 'RET-OUT-' . date('YmdHis'),
                'cr_executedby' => $ctx['userEmail'],
                'cr_notes' => $notes,
                'cr_ownerteam_id' => $ticket['cr_ownerteam_id'] ?? null,
            ]);

            $this->db->table('cr_ticket')->where('id', $ticket['id'])->update([
                'cr_returnstatus' => 'InTransit',
            ]);
            $this->db->transComplete();
            if (! $this->db->transStatus()) {
                throw new \RuntimeException('ReturnOut failed');
            }
        } catch (\Throwable $e) {
            return $this->redirectTicket($id, null, $e->getMessage());
        }

        return $this->redirectTicket($id, 'ReturnOut registered', null);
    }

    public function registerReturnIn(int $id)
    {
        $ctx = $this->bootContext();
        $ticket = $this->getTicketForAction($id, $ctx);
        if (! $ticket) {
            return $this->redirectTicket($id, null, 'Ticket not found or out of scope');
        }

        $notes = trim((string) $this->request->getPost('notes'));
        if ((int) $ticket['cr_returnrequired'] !== 1) {
            return $this->redirectTicket($id, null, 'Return is not required for this ticket');
        }

        try {
            $this->db->transStart();
            $this->db->table('cr_movement')->insert([
                'cr_ticket_id' => (int) $ticket['id'],
                'cr_asset_id' => (int) $ticket['cr_asset_id'],
                'cr_movementtype' => 'ReturnIn',
                'cr_fromsite_id' => (int) $ticket['cr_currentsite_id'],
                'cr_tosite_id' => (int) $ticket['cr_sitein_id'],
                'cr_datetime' => Time::now()->toDateTimeString(),
                'cr_reference' => 'RET-IN-' . date('YmdHis'),
                'cr_executedby' => $ctx['userEmail'],
                'cr_notes' => $notes,
                'cr_ownerteam_id' => $ticket['cr_ownerteam_id'] ?? null,
            ]);

            $this->db->table('cr_ticket')->where('id', $ticket['id'])->update([
                'cr_returnstatus' => 'Delivered',
                'cr_currentsite_id' => (int) $ticket['cr_sitein_id'],
            ]);
            $this->db->table('cr_asset')->where('id', $ticket['cr_asset_id'])->update([
                'cr_currentsite_id' => (int) $ticket['cr_sitein_id'],
            ]);
            $this->db->transComplete();
            if (! $this->db->transStatus()) {
                throw new \RuntimeException('ReturnIn failed');
            }
        } catch (\Throwable $e) {
            return $this->redirectTicket($id, null, $e->getMessage());
        }

        return $this->redirectTicket($id, 'ReturnIn registered and delivered', null);
    }

    public function createExchangeFromTicket(int $id)
    {
        $ctx = $this->bootContext();
        $ticket = $this->getTicketForAction($id, $ctx);
        if (! $ticket) {
            return $this->redirectTicket($id, null, 'Ticket not found or out of scope');
        }

        $incomingAssetId = (int) $this->request->getPost('cr_incomingasset_id');
        $replacementAssetId = (int) $this->request->getPost('cr_replacementasset_id');
        $customerPlant = (int) $this->request->getPost('cr_accountplant_id');
        $reason = trim((string) $this->request->getPost('cr_reason'));
        $approvedBy = trim((string) ($this->request->getPost('cr_approvedby') ?: $ctx['userEmail']));
        $document = trim((string) $this->request->getPost('cr_document'));

        if ($incomingAssetId <= 0 || $replacementAssetId <= 0 || $customerPlant <= 0 || $document === '') {
            return $this->redirectTicket($id, null, 'Incoming/Replacement/CustomerPlant and ExchangeDoc are required');
        }

        try {
            $this->db->transStart();

            $this->db->table('cr_ticketdocument')->insert([
                'cr_ticket_id' => (int) $ticket['id'],
                'cr_documenttype' => 'ExchangeDoc',
                'cr_file' => $document,
                'cr_author' => $ctx['userEmail'],
                'cr_createdon' => Time::now()->toDateTimeString(),
                'cr_notes' => 'Exchange document',
                'cr_ownerteam_id' => $ticket['cr_ownerteam_id'] ?? null,
            ]);

            $this->db->table('cr_assetexchange')->insert([
                'cr_ticket_id' => (int) $ticket['id'],
                'cr_accountplant_id' => $customerPlant,
                'cr_incomingasset_id' => $incomingAssetId,
                'cr_replacementasset_id' => $replacementAssetId,
                'cr_exchangedate' => Time::now()->toDateTimeString(),
                'cr_reason' => $reason,
                'cr_approvedby' => $approvedBy,
                'cr_incomingretained' => 1,
                'cr_replacementdelivered' => 1,
                'cr_ownerteam_id' => $ticket['cr_ownerteam_id'] ?? null,
            ]);

            $this->db->table('cr_asset')->where('id', $incomingAssetId)->update([
                'cr_ownertype' => 'Company',
                'cr_owneraccountplant_id' => null,
                'cr_assetstatus' => 'InRepair',
            ]);

            $this->db->table('cr_asset')->where('id', $replacementAssetId)->update([
                'cr_ownertype' => 'Customer',
                'cr_owneraccountplant_id' => $customerPlant,
                'cr_assetstatus' => 'InService',
            ]);

            $this->db->table('cr_ticket')->where('id', $ticket['id'])->update([
                'cr_outcome' => 'ExchangePerformed',
            ]);

            $this->db->table('cr_tickethistory')->insert([
                'cr_ticket_id' => (int) $ticket['id'],
                'cr_fromstatus' => (string) $ticket['cr_status'],
                'cr_tostatus' => (string) $ticket['cr_status'],
                'cr_siteattime_id' => (int) $ticket['cr_currentsite_id'],
                'cr_changedby' => $ctx['userEmail'],
                'cr_changedon' => Time::now()->toDateTimeString(),
                'cr_comment' => 'Exchange created',
                'cr_ownerteam_id' => $ticket['cr_ownerteam_id'] ?? null,
            ]);

            $this->db->transComplete();
            if (! $this->db->transStatus()) {
                throw new \RuntimeException('Exchange failed');
            }
        } catch (\Throwable $e) {
            return $this->redirectTicket($id, null, $e->getMessage());
        }

        return $this->redirectTicket($id, 'Exchange created', null);
    }

    public function ticketCreate(): string
    {
        $ctx = $this->bootContext();
        $message = null;
        $error = null;

        if (strtoupper($this->request->getMethod()) === 'POST') {
            try {
                $siteIn = (int) $this->request->getPost('cr_sitein_id');
                $site = $this->db->table('cr_site')->where('id', $siteIn)->get()->getRowArray();
                if (! $site) {
                    throw new \RuntimeException('Invalid site');
                }

                $priority = (string) ($this->request->getPost('cr_priority') ?: $site['cr_defaultpriority']);
                $sapnotice = trim((string) $this->request->getPost('cr_sapnotice'));
                $accountplantId = (int) $this->request->getPost('cr_accountplant_id');
                $assetId = (int) $this->request->getPost('cr_asset_id');
                $reportedFailure = trim((string) $this->request->getPost('cr_reportedfailure'));

                if ($sapnotice === '' || $accountplantId <= 0 || $assetId <= 0) {
                    throw new \RuntimeException('Missing required fields');
                }

                $nextId = ((int) ($this->db->table('cr_ticket')->selectMax('id')->get()->getRow('id') ?? 0)) + 1;
                $ticketNumber = 'TK-' . date('Y') . '-' . str_pad((string) $nextId, 6, '0', STR_PAD_LEFT);

                $slaHours = (int) ($this->db->table('cr_prioritysla')->where('cr_priority', $priority)->get()->getRow('cr_repairtarget_hours') ?? 48);
                $now = Time::now();

                $this->db->transStart();

                $this->db->table('cr_ticket')->insert([
                    'cr_ticketnumber' => $ticketNumber,
                    'cr_sitein_id' => $siteIn,
                    'cr_currentsite_id' => $siteIn,
                    'cr_sapnotice' => $sapnotice,
                    'cr_accountplant_id' => $accountplantId,
                    'cr_asset_id' => $assetId,
                    'cr_reportedfailure' => $reportedFailure,
                    'cr_priority' => $priority,
                    'cr_status' => $site['cr_defaultinitialstatus'],
                    'cr_receiveddate' => $now->toDateTimeString(),
                    'cr_sla_duedate' => $now->addHours($slaHours)->toDateTimeString(),
                    'cr_returnrequired' => 0,
                    'cr_isactive' => 1,
                ]);

                $ticketId = (int) $this->db->insertID();

                $this->db->table('cr_tickethistory')->insert([
                    'cr_ticket_id' => $ticketId,
                    'cr_fromstatus' => null,
                    'cr_tostatus' => $site['cr_defaultinitialstatus'],
                    'cr_siteattime_id' => $siteIn,
                    'cr_changedby' => $ctx['userEmail'],
                    'cr_changedon' => $now->toDateTimeString(),
                    'cr_comment' => 'Initial creation from responsive app',
                ]);

                $this->db->transComplete();

                if (! $this->db->transStatus()) {
                    throw new \RuntimeException('Transaction failed');
                }

                $message = 'Ticket created: ' . $ticketNumber;
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return $this->renderScreen('ticket_create', [
            'ctx' => $ctx,
            'sites' => $this->getSites(),
            'accountPlants' => $this->getAccountPlants(),
            'assets' => $this->getAssets(),
            'message' => $message,
            'error' => $error,
        ]);
    }

    public function assetsList(): string
    {
        $ctx = $this->bootContext();
        $assets = [];

        try {
            $assets = $this->db->table('cr_asset a')
                ->select('a.id, a.cr_assetcode, a.cr_serialnumber, a.cr_model, a.cr_assetstatus, s.cr_sitecode')
                ->join('cr_site s', 's.id = a.cr_currentsite_id', 'left')
                ->orderBy('a.id', 'DESC')
                ->limit(100)
                ->get()
                ->getResultArray();
        } catch (DatabaseException) {
        }

        return $this->renderScreen('assets_list', [
            'ctx' => $ctx,
            'assets' => $assets,
        ]);
    }

    public function asset360(int $id): string
    {
        $ctx = $this->bootContext();
        $asset = null;
        $tickets = [];
        $movements = [];

        try {
            $asset = $this->db->table('cr_asset a')
                ->select('a.*, s.cr_sitecode, ap.cr_name AS owner_name')
                ->join('cr_site s', 's.id = a.cr_currentsite_id', 'left')
                ->join('cr_accountplant ap', 'ap.id = a.cr_owneraccountplant_id', 'left')
                ->where('a.id', $id)
                ->get()->getRowArray();

            if ($asset) {
                $tickets = $this->db->table('cr_ticket')
                    ->select('id, cr_ticketnumber, cr_status, cr_priority, cr_receiveddate')
                    ->where('cr_asset_id', $id)
                    ->orderBy('id', 'DESC')
                    ->get()->getResultArray();

                $movements = $this->db->table('cr_movement')
                    ->select('id, cr_movementtype, cr_datetime, cr_reference')
                    ->where('cr_asset_id', $id)
                    ->orderBy('id', 'DESC')
                    ->get()->getResultArray();
            }
        } catch (DatabaseException) {
        }

        return $this->renderScreen('asset_360', [
            'ctx' => $ctx,
            'asset' => $asset,
            'tickets' => $tickets,
            'movements' => $movements,
        ]);
    }

    public function movementsList(): string
    {
        $ctx = $this->bootContext();
        $movements = [];

        try {
            $movements = $this->db->table('cr_movement m')
                ->select('m.id, m.cr_movementtype, m.cr_datetime, m.cr_reference, t.cr_ticketnumber, a.cr_serialnumber')
                ->join('cr_ticket t', 't.id = m.cr_ticket_id', 'left')
                ->join('cr_asset a', 'a.id = m.cr_asset_id', 'left')
                ->orderBy('m.id', 'DESC')
                ->limit(100)
                ->get()
                ->getResultArray();
        } catch (DatabaseException) {
        }

        return $this->renderScreen('movements_list', [
            'ctx' => $ctx,
            'movements' => $movements,
        ]);
    }

    public function movementCreate(): string
    {
        $ctx = $this->bootContext();
        $message = null;
        $error = null;

        if (strtoupper($this->request->getMethod()) === 'POST') {
            try {
                $this->db->table('cr_movement')->insert([
                    'cr_ticket_id' => (int) $this->request->getPost('cr_ticket_id') ?: null,
                    'cr_asset_id' => (int) $this->request->getPost('cr_asset_id'),
                    'cr_movementtype' => (string) $this->request->getPost('cr_movementtype'),
                    'cr_fromsite_id' => (int) $this->request->getPost('cr_fromsite_id') ?: null,
                    'cr_tosite_id' => (int) $this->request->getPost('cr_tosite_id') ?: null,
                    'cr_datetime' => Time::now()->toDateTimeString(),
                    'cr_reference' => trim((string) $this->request->getPost('cr_reference')),
                    'cr_executedby' => $ctx['userEmail'],
                    'cr_notes' => trim((string) $this->request->getPost('cr_notes')),
                ]);
                $message = 'Movement created';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return $this->renderScreen('movement_create', [
            'ctx' => $ctx,
            'sites' => $this->getSites(),
            'assets' => $this->getAssets(),
            'tickets' => $this->getTicketsSimple(),
            'message' => $message,
            'error' => $error,
        ]);
    }

    public function partsRequestsList(): string
    {
        $ctx = $this->bootContext();
        $parts = [];

        try {
            $parts = $this->db->table('cr_partrequest pr')
                ->select('pr.id, pr.cr_partname, pr.cr_quantity, pr.cr_status, pr.cr_requestedon, t.cr_ticketnumber')
                ->join('cr_ticket t', 't.id = pr.cr_ticket_id', 'left')
                ->orderBy('pr.id', 'DESC')
                ->limit(100)
                ->get()
                ->getResultArray();
        } catch (DatabaseException) {
        }

        return $this->renderScreen('parts_requests_list', [
            'ctx' => $ctx,
            'parts' => $parts,
        ]);
    }

    public function exchangeCreate(): string
    {
        $ctx = $this->bootContext();
        $message = null;
        $error = null;

        if (strtoupper($this->request->getMethod()) === 'POST') {
            try {
                $this->db->table('cr_assetexchange')->insert([
                    'cr_ticket_id' => (int) $this->request->getPost('cr_ticket_id'),
                    'cr_accountplant_id' => (int) $this->request->getPost('cr_accountplant_id'),
                    'cr_incomingasset_id' => (int) $this->request->getPost('cr_incomingasset_id'),
                    'cr_replacementasset_id' => (int) $this->request->getPost('cr_replacementasset_id'),
                    'cr_exchangedate' => Time::now()->toDateTimeString(),
                    'cr_reason' => trim((string) $this->request->getPost('cr_reason')),
                    'cr_approvedby' => $ctx['userEmail'],
                    'cr_incomingretained' => 1,
                    'cr_replacementdelivered' => 1,
                ]);
                $message = 'Exchange created';
            } catch (\Throwable $e) {
                $error = $e->getMessage();
            }
        }

        return $this->renderScreen('exchange_create', [
            'ctx' => $ctx,
            'message' => $message,
            'error' => $error,
            'tickets' => $this->getTicketsSimple(),
            'accountPlants' => $this->getAccountPlants(),
            'assets' => $this->getAssets(),
        ]);
    }

    public function nonConformitiesList(): string
    {
        $ctx = $this->bootContext();
        $items = [];

        try {
            $items = $this->db->table('cr_nonconformity nc')
                ->select('nc.id, nc.cr_type, nc.cr_status, nc.cr_closedon, t.cr_ticketnumber')
                ->join('cr_ticket t', 't.id = nc.cr_ticket_id', 'left')
                ->orderBy('nc.id', 'DESC')
                ->limit(100)
                ->get()
                ->getResultArray();
        } catch (DatabaseException) {
        }

        return $this->renderScreen('nonconformities_list', [
            'ctx' => $ctx,
            'items' => $items,
        ]);
    }

    public function peopleAdmin(): string
    {
        $ctx = $this->bootContext();
        $people = [];

        try {
            $people = $this->db->table('cr_person')
                ->select('id, cr_fullname, cr_email, cr_phone, cr_isactive')
                ->orderBy('id', 'DESC')
                ->get()->getResultArray();
        } catch (DatabaseException) {
        }

        return $this->renderScreen('people_admin', [
            'ctx' => $ctx,
            'people' => $people,
        ]);
    }

    public function settingsUser(): string
    {
        $ctx = $this->bootContext();
        $message = null;

        if (strtoupper($this->request->getMethod()) === 'POST') {
            try {
                $this->db->table('cr_usersettings')
                    ->where('cr_useremail', $ctx['userEmail'])
                    ->update([
                        'cr_languagemode' => (string) $this->request->getPost('cr_languagemode'),
                        'cr_preferredlanguage' => (string) $this->request->getPost('cr_preferredlanguage'),
                        'cr_defaultsite_id' => (int) $this->request->getPost('cr_defaultsite_id') ?: null,
                    ]);
                $message = 'Settings updated';
                $ctx = $this->bootContext();
            } catch (DatabaseException) {
            }
        }

        return $this->renderScreen('settings_user', [
            'ctx' => $ctx,
            'sites' => $this->getSites(),
            'message' => $message,
        ]);
    }

    public function settingsSystem(): string
    {
        $ctx = $this->bootContext();

        return $this->renderScreen('settings_system', [
            'ctx' => $ctx,
            'sites' => $this->getSites(),
            'sla' => $this->getSlaRows(),
            'translations' => $this->getTranslationRows(),
        ]);
    }

    public function search(): string
    {
        $ctx = $this->bootContext();
        $q = trim((string) $this->request->getGet('q'));
        $results = [];

        if ($q !== '') {
            try {
                $builder = $this->db->table('cr_ticket t')
                    ->select('t.id, t.cr_ticketnumber, t.cr_sapnotice, a.cr_serialnumber, t.cr_status')
                    ->join('cr_asset a', 'a.id = t.cr_asset_id', 'left')
                    ->groupStart()
                    ->like('t.cr_ticketnumber', $q)
                    ->orLike('t.cr_sapnotice', $q)
                    ->orLike('a.cr_serialnumber', $q)
                    ->groupEnd()
                    ->limit(25);

                $this->applyTicketScope($builder, $ctx, 't');
                $results = $builder->get()->getResultArray();
            } catch (DatabaseException) {
            }
        }

        return $this->renderScreen('search', [
            'ctx' => $ctx,
            'q' => $q,
            'results' => $results,
        ]);
    }

    private $db;

    private function bootContext(): array
    {
        $this->db = db_connect();

        $userEmail = strtolower(trim((string) ($this->request->getGet('user') ?: 'ana.recepcion@demo.local')));

        $settings = $this->db->table('cr_usersettings')->where('cr_useremail', $userEmail)->get()->getRowArray() ?? [];

        $langMode = $settings['cr_languagemode'] ?? 'Auto';
        $lang = 'en';

        if ($langMode === 'Manual' && isset($settings['cr_preferredlanguage'])) {
            $lang = (string) $settings['cr_preferredlanguage'];
        } else {
            $locale = strtolower(substr((string) $this->request->getLocale(), 0, 2));
            $lang = in_array($locale, ['es', 'pt', 'it', 'en'], true) ? $locale : 'en';
        }

        $labels = [];
        try {
            $rows = $this->db->table('cr_localizationstring')->where('cr_isactive', 1)->get()->getResultArray();
            $col = 'cr_text_' . $lang;
            foreach ($rows as $row) {
                $labels[$row['cr_key']] = $row[$col] ?? ($row['cr_text_en'] ?? $row['cr_key']);
            }
        } catch (DatabaseException) {
        }

        $roles = [];
        $permissions = [];
        try {
            $roles = array_map(
                static fn(array $r) => (string) $r['cr_name'],
                $this->db->table('cr_security_user_role ur')
                    ->select('r.cr_name')
                    ->join('cr_security_role r', 'r.id = ur.cr_role_id')
                    ->where('ur.cr_useremail', $userEmail)
                    ->where('ur.cr_isactive', 1)
                    ->get()->getResultArray()
            );
        } catch (DatabaseException) {
        }

        try {
            $permissionRows = $this->db->table('cr_security_user_role ur')
                ->select('p.cr_resource, MAX(p.cr_can_create) cr_can_create, MAX(p.cr_can_read) cr_can_read, MAX(p.cr_can_update) cr_can_update, MAX(p.cr_can_delete) cr_can_delete, MAX(p.cr_can_change_status) cr_can_change_status, MAX(p.cr_can_close_technical) cr_can_close_technical, MAX(p.cr_can_close_administrative) cr_can_close_administrative, MAX(p.cr_can_approve) cr_can_approve, MAX(p.cr_can_manage_sla) cr_can_manage_sla, MAX(p.cr_scope) cr_scope')
                ->join('cr_security_table_permission p', 'p.cr_role_id = ur.cr_role_id')
                ->where('ur.cr_useremail', $userEmail)
                ->where('ur.cr_isactive', 1)
                ->groupBy('p.cr_resource')
                ->get()->getResultArray();

            foreach ($permissionRows as $row) {
                $permissions[$row['cr_resource']] = $row;
            }
        } catch (DatabaseException) {
        }

        try {
            $activeRole = $roles[0] ?? 'cr_Recepcion';
            $isAdmin = in_array('cr_AdminSistema', $roles, true) ? 1 : 0;
            $this->db->query('SET @app_useremail = ?', [$userEmail]);
            $this->db->query('SET @app_role_name = ?', [$activeRole]);
            $this->db->query('SET @app_is_admin = ?', [$isAdmin]);
        } catch (DatabaseException) {
        }

        return [
            'userEmail' => $userEmail,
            'settings' => $settings,
            'langMode' => $langMode,
            'lang' => $lang,
            'labels' => $labels,
            'roles' => $roles,
            'activeRole' => $roles[0] ?? 'cr_Recepcion',
            'permissions' => $permissions,
            'isGlobal' => $this->hasGlobalScope($roles),
            'defaultSiteId' => isset($settings['cr_defaultsite_id']) ? (int) $settings['cr_defaultsite_id'] : null,
        ];
    }

    private function hasGlobalScope(array $roles): bool
    {
        return in_array('cr_Coordinacion', $roles, true)
            || in_array('cr_Calidad', $roles, true)
            || in_array('cr_AdminSistema', $roles, true);
    }

    private function applyTicketScope(BaseBuilder $builder, array $ctx, string $alias = 'cr_ticket'): void
    {
        if ($ctx['isGlobal']) {
            return;
        }

        if (! empty($ctx['defaultSiteId'])) {
            $builder->groupStart()
                ->where($alias . '.cr_currentsite_id', $ctx['defaultSiteId'])
                ->orWhere($alias . '.cr_sitein_id', $ctx['defaultSiteId'])
                ->groupEnd();
        }
    }

    private function buildTimeline(int $ticketId): array
    {
        $timeline = [];

        try {
            $history = $this->db->table('cr_tickethistory')
                ->select("cr_changedon AS event_date, 'HISTORY' AS event_type, cr_comment AS event_text", false)
                ->where('cr_ticket_id', $ticketId)
                ->get()->getResultArray();

            $worklog = $this->db->table('cr_worklog')
                ->select("cr_start AS event_date, 'WORKLOG' AS event_type, cr_notes AS event_text", false)
                ->where('cr_ticket_id', $ticketId)
                ->get()->getResultArray();

            $movements = $this->db->table('cr_movement')
                ->select("cr_datetime AS event_date, 'MOVEMENT' AS event_type, cr_notes AS event_text", false)
                ->where('cr_ticket_id', $ticketId)
                ->get()->getResultArray();

            $timeline = array_merge($history, $worklog, $movements);
            usort($timeline, static fn(array $a, array $b): int => strcmp((string) $b['event_date'], (string) $a['event_date']));
        } catch (DatabaseException) {
        }

        return $timeline;
    }

    private function getTicketForAction(int $id, array $ctx): ?array
    {
        $builder = $this->db->table('cr_ticket t')
            ->select('t.*')
            ->where('t.id', $id)
            ->limit(1);
        $this->applyTicketScope($builder, $ctx, 't');
        $row = $builder->get()->getRowArray();
        return $row ?: null;
    }

    private function redirectTicket(int $ticketId, ?string $message, ?string $error)
    {
        $query = ['user' => (string) $this->request->getGet('user')];
        if ($message) {
            $query['message'] = $message;
        }
        if ($error) {
            $query['error'] = $error;
        }
        return redirect()->to('/app/ticket/' . $ticketId . '?' . http_build_query($query));
    }

    private function validateStatusTransition(array $ticket, string $newStatus, array $ctx): void
    {
        if ((string) $ticket['cr_status'] === $newStatus) {
            return;
        }
        if (in_array('cr_AdminSistema', $ctx['roles'], true)) {
            return;
        }

        $allowed = (int) ($this->db->table('cr_security_ticket_status_rule sr')
            ->select('COUNT(*) AS total', false)
            ->join('cr_security_role r', 'r.id = sr.cr_role_id')
            ->where('r.cr_name', $ctx['activeRole'])
            ->where('sr.cr_from_status', $ticket['cr_status'])
            ->where('sr.cr_to_status', $newStatus)
            ->where('sr.cr_isallowed', 1)
            ->get()->getRow('total') ?? 0);

        if ($allowed === 0) {
            throw new \RuntimeException('Transition not allowed for role');
        }
    }

    private function validateStatusBusinessRules(array $ticket, string $newStatus, string $comment, array $patch, array $ctx): void
    {
        $site = $this->db->table('cr_site')->where('id', $ticket['cr_currentsite_id'])->get()->getRowArray() ?? [];
        $asset = $this->db->table('cr_asset')->where('id', $ticket['cr_asset_id'])->get()->getRowArray() ?? [];
        $reported = strtolower((string) ($ticket['cr_reportedfailure'] ?? '') . ' ' . ($ticket['cr_detectedfailure'] ?? ''));

        if (str_contains($reported, 'transport') && ! in_array($newStatus, ['Received', 'Diagnosis'], true)) {
            if (! $this->existsDocument((int) $ticket['id'], 'TransportDamagePhoto')) {
                throw new \RuntimeException('TransportDamagePhoto is required before advancing');
            }
        }

        if ($newStatus === 'Diagnosis') {
            if (! $asset || (int) ($asset['cr_isactive'] ?? 0) !== 1 || (int) $ticket['cr_accountplant_id'] <= 0 || (int) $ticket['cr_asset_id'] <= 0 || trim((string) $ticket['cr_sapnotice']) === '') {
                throw new \RuntimeException('Diagnosis requires active asset and complete intake data');
            }
        }

        if ($newStatus === 'WaitingParts') {
            $hasParts = (int) ($this->db->table('cr_partrequest')
                ->select('COUNT(*) AS total', false)
                ->where('cr_ticket_id', $ticket['id'])
                ->whereIn('cr_status', ['Requested', 'Ordered'])
                ->get()->getRow('total') ?? 0);
            if ($hasParts === 0 && strlen($comment) < 8) {
                throw new \RuntimeException('WaitingParts requires part request or justified comment');
            }
        }

        if ($newStatus === 'RepairInProgress') {
            $hasWorklog = (int) ($this->db->table('cr_worklog')
                ->select('COUNT(*) AS total', false)
                ->where('cr_ticket_id', $ticket['id'])
                ->where('cr_start IS NOT NULL')
                ->get()->getRow('total') ?? 0);
            if ($hasWorklog === 0) {
                $hasTech = (int) ($this->db->table('cr_person_role_site prs')
                    ->select('COUNT(*) AS total', false)
                    ->join('cr_role r', 'r.id = prs.cr_role_id')
                    ->where('prs.cr_site_id', $ticket['cr_currentsite_id'])
                    ->where('r.cr_name', 'Tecnico')
                    ->where('prs.cr_enddate IS NULL')
                    ->get()->getRow('total') ?? 0);
                if ($hasTech === 0) {
                    throw new \RuntimeException('RepairInProgress requires assigned technician or worklog start');
                }
            }
        }

        if ($newStatus === 'Testing' && (int) ($site['cr_requiresolutiontoclose'] ?? 0) === 1) {
            $solution = $patch['cr_repairsolution'] !== '' ? $patch['cr_repairsolution'] : (string) ($ticket['cr_repairsolution'] ?? '');
            if (trim($solution) === '') {
                throw new \RuntimeException('Testing requires repair solution for this site');
            }
        }

        if ($newStatus === 'ReadyToShip' && (int) ($site['cr_requiretestingchecklisttoship'] ?? 0) === 1) {
            if (! $this->existsDocument((int) $ticket['id'], 'TestEvidence')) {
                throw new \RuntimeException('ReadyToShip requires TestEvidence');
            }
        }

        if ($newStatus === 'Shipped') {
            $hasShippingMovement = (int) ($this->db->table('cr_movement')
                ->select('COUNT(*) AS total', false)
                ->where('cr_ticket_id', $ticket['id'])
                ->where('cr_movementtype', 'ShipToCustomer')
                ->get()->getRow('total') ?? 0);
            if ($hasShippingMovement === 0) {
                throw new \RuntimeException('Shipped requires logistic movement ShipToCustomer');
            }
            if ((int) ($site['cr_requiretestingchecklisttoship'] ?? 0) === 1 && ! $this->existsDocument((int) $ticket['id'], 'ShippingDoc')) {
                throw new \RuntimeException('Shipped requires ShippingDoc for this site policy');
            }
        }

        if ($newStatus === 'Closed') {
            $adminDone = (int) $patch['cr_administrativeclosuredone'] === 1 || (int) $ticket['cr_administrativeclosuredone'] === 1;
            $techReady = (int) $patch['cr_technicalclosureready'] === 1 || (int) $ticket['cr_technicalclosureready'] === 1;
            if (! $adminDone || ! $techReady) {
                throw new \RuntimeException('Closed requires technical and administrative closure flags');
            }

            if ((int) $ticket['cr_returnrequired'] === 1) {
                $returnStatus = (string) ($ticket['cr_returnstatus'] ?? '');
                if ($returnStatus !== 'Delivered') {
                    throw new \RuntimeException('Closed requires return delivered');
                }
                $hasReturnIn = (int) ($this->db->table('cr_movement')
                    ->select('COUNT(*) AS total', false)
                    ->where('cr_ticket_id', $ticket['id'])
                    ->where('cr_movementtype', 'ReturnIn')
                    ->where('cr_tosite_id', $ticket['cr_sitein_id'])
                    ->get()->getRow('total') ?? 0);
                if ($hasReturnIn === 0) {
                    throw new \RuntimeException('Closed requires ReturnIn movement to sitein');
                }
            }

            if ((int) ($site['cr_requiresolutiontoclose'] ?? 0) === 1) {
                $solution = $patch['cr_repairsolution'] !== '' ? $patch['cr_repairsolution'] : (string) ($ticket['cr_repairsolution'] ?? '');
                if (trim($solution) === '') {
                    throw new \RuntimeException('Closed requires repair solution by site policy');
                }
            }
            if ((int) ($site['cr_requirematerialstoclose'] ?? 0) === 1) {
                $materials = (int) ($this->db->table('cr_ticketmaterial')
                    ->select('COUNT(*) AS total', false)
                    ->where('cr_ticket_id', $ticket['id'])
                    ->get()->getRow('total') ?? 0);
                if ($materials === 0) {
                    throw new \RuntimeException('Closed requires materials registered by site policy');
                }
            }
            if ((int) ($site['cr_requiretestingchecklisttoship'] ?? 0) === 1 && ! $this->existsDocument((int) $ticket['id'], 'TestEvidence')) {
                throw new \RuntimeException('Closed requires test checklist/evidence by site policy');
            }
        }

        if ((int) $patch['cr_nonrepairable'] === 1) {
            $reason = $patch['cr_cancelreason'] !== '' ? $patch['cr_cancelreason'] : (string) ($ticket['cr_cancelreason'] ?? '');
            if ($reason === '') {
                throw new \RuntimeException('NonRepairable requires reason');
            }
            if (! in_array($ctx['activeRole'], ['cr_Coordinacion', 'cr_Calidad', 'cr_AdminSistema'], true)) {
                throw new \RuntimeException('NonRepairable requires Coordinacion/Calidad approval');
            }
        }
    }

    private function existsDocument(int $ticketId, string $type): bool
    {
        $count = (int) ($this->db->table('cr_ticketdocument')
            ->select('COUNT(*) AS total', false)
            ->where('cr_ticket_id', $ticketId)
            ->where('cr_documenttype', $type)
            ->get()->getRow('total') ?? 0);
        return $count > 0;
    }

    private function renderScreen(string $screen, array $data): string
    {
        $data['screen'] = $screen;
        return view('repair/layout', $data);
    }

    private function getSites(): array
    {
        try {
            return $this->db->table('cr_site')->select('id, cr_sitecode, cr_name')->orderBy('cr_sitecode', 'ASC')->get()->getResultArray();
        } catch (DatabaseException) {
            return [];
        }
    }

    private function getAssets(): array
    {
        try {
            return $this->db->table('cr_asset')->select('id, cr_serialnumber, cr_model')->orderBy('id', 'DESC')->limit(100)->get()->getResultArray();
        } catch (DatabaseException) {
            return [];
        }
    }

    private function getAccountPlants(): array
    {
        try {
            return $this->db->table('cr_accountplant')->select('id, cr_name')->orderBy('cr_name', 'ASC')->get()->getResultArray();
        } catch (DatabaseException) {
            return [];
        }
    }

    private function getTicketsSimple(): array
    {
        try {
            return $this->db->table('cr_ticket')->select('id, cr_ticketnumber')->orderBy('id', 'DESC')->limit(100)->get()->getResultArray();
        } catch (DatabaseException) {
            return [];
        }
    }

    private function getSlaRows(): array
    {
        try {
            return $this->db->table('cr_prioritysla')->orderBy('id', 'ASC')->get()->getResultArray();
        } catch (DatabaseException) {
            return [];
        }
    }

    private function getTranslationRows(): array
    {
        try {
            return $this->db->table('cr_localizationstring')->limit(100)->get()->getResultArray();
        } catch (DatabaseException) {
            return [];
        }
    }
}
