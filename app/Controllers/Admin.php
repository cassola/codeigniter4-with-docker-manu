<?php

namespace App\Controllers;

class Admin extends BaseController
{
    public function config()
    {
        $configs = db_connect()->table('rm_siteconfiguration cfg')
            ->select('cfg.*, s.rm_sitecode, s.rm_name')
            ->join('rm_site s', 's.id = cfg.rm_site_id')
            ->orderBy('s.rm_sitecode')
            ->get()
            ->getResultArray();

        return view('layouts/powerapp', [
            'title' => t('admin_site_config'),
            'area' => t('admin'),
            'commandActions' => [
                ['label' => t('dashboard'), 'href' => site_url('dashboard')],
                ['label' => t('master_data'), 'href' => site_url('master-data')],
            ],
            'content' => view('admin/config', ['configs' => $configs]),
        ]);
    }
}
