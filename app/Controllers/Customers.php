<?php

namespace App\Controllers;

class Customers extends BaseController
{
    public function index()
    {
        $db = db_connect();

        $customers = $db->table('rm_customer c')
            ->select('c.*, COUNT(p.id) AS plants')
            ->join('rm_customerplant p', 'p.rm_customer_id = c.id', 'left')
            ->groupBy('c.id')
            ->orderBy('c.rm_legalname')
            ->get()
            ->getResultArray();

        $plants = $db->table('rm_customerplant p')
            ->select('p.*, c.rm_legalname')
            ->join('rm_customer c', 'c.id = p.rm_customer_id')
            ->orderBy('p.rm_name')
            ->get()
            ->getResultArray();

        return view('layouts/powerapp', [
            'title' => t('customers'),
            'area' => t('customers'),
            'commandActions' => [
                ['label' => t('master_data'), 'href' => site_url('master-data')],
                ['label' => t('tickets'), 'href' => site_url('tickets')],
            ],
            'content' => view('customers/index', [
                'customers' => $customers,
                'plants' => $plants,
            ]),
        ]);
    }
}
