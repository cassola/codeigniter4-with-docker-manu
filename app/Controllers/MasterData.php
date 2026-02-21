<?php

namespace App\Controllers;

class MasterData extends BaseController
{
    public function index()
    {
        $db = db_connect();

        return view('layouts/powerapp', [
            'title' => t('master_data'),
            'area' => t('master_data'),
            'commandActions' => [
                ['label' => t('tickets'), 'href' => site_url('tickets')],
                ['label' => t('admin'), 'href' => site_url('admin/config')],
            ],
            'content' => view('master_data/index', [
                'sites' => $db->table('rm_site')->orderBy('rm_sitecode')->get()->getResultArray(),
                'models' => $db->table('rm_productmodel')->orderBy('rm_modelcode')->get()->getResultArray(),
                'materials' => $db->table('rm_material')->orderBy('rm_partnumber')->get()->getResultArray(),
            ]),
        ]);
    }
}
