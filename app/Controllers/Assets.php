<?php

namespace App\Controllers;

class Assets extends BaseController
{
    public function index()
    {
        $assets = db_connect()->table('rm_asset a')
            ->select('a.*, pm.rm_modelcode, s.rm_sitecode AS current_site')
            ->join('rm_productmodel pm', 'pm.id = a.rm_model_id')
            ->join('rm_site s', 's.id = a.rm_currentlocationsite_id', 'left')
            ->orderBy('a.updated_at', 'DESC')
            ->get()
            ->getResultArray();

        return view('layouts/powerapp', [
            'title' => t('assets'),
            'area' => t('assets'),
            'commandActions' => [
                ['label' => t('open_tickets'), 'href' => site_url('tickets')],
                ['label' => t('dashboard'), 'href' => site_url('dashboard')],
            ],
            'content' => view('assets/index', ['assets' => $assets]),
        ]);
    }
}
