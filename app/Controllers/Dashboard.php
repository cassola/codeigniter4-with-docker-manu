<?php

namespace App\Controllers;

class Dashboard extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $statusData = $db->table('rm_repairticket')
            ->select('rm_status, COUNT(*) AS total')
            ->groupBy('rm_status')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        $siteData = $db->table('rm_repairticket t')
            ->select('s.rm_sitecode, s.rm_name, COUNT(*) AS total')
            ->join('rm_site s', 's.id = t.rm_currentprocessingsite_id')
            ->groupBy('s.id, s.rm_sitecode, s.rm_name')
            ->orderBy('total', 'DESC')
            ->get()
            ->getResultArray();

        $kpis = [
            'totalTickets' => (int) $db->table('rm_repairticket')->countAll(),
            'openTickets' => (int) $db->table('rm_repairticket')->whereNotIn('rm_status', ['Closed', 'Cancelled'])->countAllResults(),
            'assetsInRepair' => (int) $db->table('rm_asset')->where('rm_status', 'InRepair')->countAllResults(),
            'materialsUsed' => (int) $db->table('rm_ticketmaterial')->countAll(),
        ];

        return view('layouts/powerapp', [
            'title' => t('repair_dashboard'),
            'area' => t('reports'),
            'commandActions' => [
                ['label' => t('open_tickets'), 'href' => site_url('tickets')],
                ['label' => t('new_ticket'), 'href' => site_url('tickets/new')],
            ],
            'content' => view('dashboard/index', [
                'kpis' => $kpis,
                'statusData' => $statusData,
                'siteData' => $siteData,
            ]),
        ]);
    }
}
