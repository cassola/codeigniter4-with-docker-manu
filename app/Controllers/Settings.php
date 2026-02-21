<?php

namespace App\Controllers;

class Settings extends BaseController
{
    public function index()
    {
        $db = db_connect();
        $sites = $db->table('rm_site')
            ->orderBy('rm_sitecode')
            ->get()
            ->getResultArray();
        $users = $db->table('rm_user')
            ->select('id, full_name, username')
            ->orderBy('full_name')
            ->get()
            ->getResultArray();
        $userIds = array_map(static fn ($user): int => (int) $user['id'], $users);
        $requestedUserId = (int) $this->request->getGet('current_user_id');
        $selectedUserId = in_array($requestedUserId, $userIds, true)
            ? $requestedUserId
            : $this->resolveCurrentUserId($db);

        $currentUser = $selectedUserId > 0
            ? $db->table('rm_user')->select('id, full_name, username')->where('id', $selectedUserId)->get()->getRowArray()
            : null;
        $preferences = null;
        if ($selectedUserId > 0 && $db->tableExists('rm_userpreference')) {
            $preferences = $db->table('rm_userpreference')->where('rm_user_id', $selectedUserId)->get()->getRowArray();
        }

        $selectedLocale = (string) ($preferences['rm_default_locale'] ?? session('locale') ?? 'en');
        $selectedDefaultSiteId = (int) ($preferences['rm_default_site_id'] ?? 0);
        session()->set([
            'current_user_id' => $selectedUserId,
            'current_user_name' => (string) ($currentUser['full_name'] ?? ''),
            'current_username' => (string) ($currentUser['username'] ?? ''),
            'locale' => $selectedLocale,
            'default_site_id' => $selectedDefaultSiteId,
            'prefs_loaded' => true,
        ]);
        service('request')->setLocale($selectedLocale);
        service('language')->setLocale($selectedLocale);

        return view('layouts/powerapp', [
            'title' => t('settings_title'),
            'area' => t('settings'),
            'commandActions' => [
                ['label' => t('save_settings'), 'href' => '#settings-form'],
                ['label' => t('dashboard'), 'href' => site_url('dashboard')],
            ],
            'content' => view('settings/index', [
                'sites' => $sites,
                'users' => $users,
                'locale' => $selectedLocale,
                'defaultSiteId' => $selectedDefaultSiteId,
                'currentUserId' => $selectedUserId,
                'currentUser' => $currentUser,
            ]),
        ]);
    }

    public function save()
    {
        $db = db_connect();
        $selectedUserId = (int) $this->request->getPost('current_user_id');
        $locale = (string) $this->request->getPost('locale');
        $defaultSiteId = (int) $this->request->getPost('default_site_id');
        $supported = ['es', 'pt', 'en', 'it'];

        if ($selectedUserId <= 0) {
            return redirect()->back()->withInput()->with('error', t('error_invalid_user'));
        }

        $userExists = $db->table('rm_user')->where('id', $selectedUserId)->countAllResults() > 0;
        if (! $userExists) {
            return redirect()->back()->withInput()->with('error', t('error_invalid_user'));
        }

        if (! in_array($locale, $supported, true)) {
            return redirect()->back()->withInput()->with('error', t('error_invalid_locale'));
        }

        if ($defaultSiteId > 0) {
            $siteExists = $db->table('rm_site')->where('id', $defaultSiteId)->countAllResults() > 0;
            if (! $siteExists) {
                return redirect()->back()->withInput()->with('error', t('error_invalid_site'));
            }
        }

        $currentUserId = $selectedUserId;
        if ($currentUserId <= 0) {
            return redirect()->back()->withInput()->with('error', t('error_no_users'));
        }

        if (! $db->tableExists('rm_userpreference')) {
            return redirect()->back()->withInput()->with('error', t('error_preferences_table_missing'));
        }

        $now = date('Y-m-d H:i:s');
        $preferences = [
            'rm_default_locale' => $locale,
            'rm_default_site_id' => $defaultSiteId > 0 ? $defaultSiteId : null,
            'updated_at' => $now,
        ];

        $exists = $db->table('rm_userpreference')
            ->where('rm_user_id', $currentUserId)
            ->countAllResults() > 0;

        if ($exists) {
            $db->table('rm_userpreference')->where('rm_user_id', $currentUserId)->update($preferences);
        } else {
            $db->table('rm_userpreference')->insert($preferences + [
                'rm_user_id' => $currentUserId,
                'created_at' => $now,
            ]);
        }

        $userRow = $db->table('rm_user')->select('full_name, username')->where('id', $currentUserId)->get()->getRowArray();

        session()->set([
            'current_user_id' => $currentUserId,
            'current_user_name' => (string) ($userRow['full_name'] ?? ''),
            'current_username' => (string) ($userRow['username'] ?? ''),
            'locale' => $locale,
            'default_site_id' => $defaultSiteId,
        ]);

        return redirect()->to(site_url('settings'))->with('success', t('msg_settings_saved'));
    }

    private function resolveCurrentUserId($db): int
    {
        $currentUserId = (int) (session('current_user_id') ?? 0);
        if ($currentUserId > 0) {
            return $currentUserId;
        }

        $user = $db->table('rm_user')->select('id')->orderBy('id')->get(1)->getRowArray();
        if (! $user) {
            return 0;
        }

        $currentUserId = (int) $user['id'];
        session()->set('current_user_id', $currentUserId);

        return $currentUserId;
    }
}
