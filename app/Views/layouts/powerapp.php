<!doctype html>
<html lang="<?= esc(service('request')->getLocale()) ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= esc($title ?? t('app_name')) ?></title>
    <style>
        :root {
            --bg: #f3f2f1;
            --panel: #ffffff;
            --line: #edebe9;
            --text: #323130;
            --muted: #605e5c;
            --brand: #0078d4;
            --brand-dark: #005a9e;
            --ok: #107c10;
            --warn: #a4262c;
        }
        * { box-sizing: border-box; }
        body { margin: 0; font-family: Segoe UI, Arial, sans-serif; color: var(--text); background: var(--bg); }
        .top { background: #ffffff; border-bottom: 1px solid var(--line); padding: 10px 16px; font-size: 14px; }
        .top b { color: var(--brand-dark); }
        .top-config { float: right; margin-left: 8px; text-decoration: none; font-size: 12px; color: var(--brand-dark); border: 1px solid var(--line); border-radius: 4px; padding: 2px 8px; background: #fff; }
        .lang-switch { float: right; display: flex; gap: 6px; align-items: center; }
        .lang-switch a { text-decoration: none; font-size: 12px; color: var(--brand-dark); border: 1px solid var(--line); border-radius: 4px; padding: 2px 6px; background: #fff; }
        .lang-switch a.active { background: #e8f3ff; border-color: #b9d7f5; font-weight: 600; }
        .wrap { display: grid; grid-template-columns: 220px 1fr; min-height: calc(100vh - 44px); }
        .nav { background: #fff; border-right: 1px solid var(--line); padding: 14px; }
        .nav h3 { margin: 4px 0 10px; font-size: 13px; color: var(--muted); text-transform: uppercase; letter-spacing: 0.4px; }
        .nav a { display: block; padding: 9px 10px; margin-bottom: 4px; border-radius: 6px; text-decoration: none; color: var(--text); }
        .nav a:hover { background: #f5f9ff; }
        .nav a.active { background: #e8f3ff; color: var(--brand-dark); font-weight: 600; }
        .main { padding: 14px; }
        .command { background: #fff; border: 1px solid var(--line); border-radius: 8px; margin-bottom: 14px; padding: 10px; display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .btn { border: 1px solid var(--line); background: #fff; color: var(--text); padding: 8px 12px; border-radius: 5px; text-decoration: none; font-size: 13px; }
        .btn.primary { background: var(--brand); color: #fff; border-color: var(--brand); }
        .btn.primary:hover { background: var(--brand-dark); }
        .card { background: var(--panel); border: 1px solid var(--line); border-radius: 8px; padding: 12px; margin-bottom: 12px; }
        .title { font-size: 22px; margin: 0 0 10px; }
        .grid { display: grid; gap: 12px; }
        .grid.cols-2 { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .grid.cols-4 { grid-template-columns: repeat(4, minmax(0, 1fr)); }
        table { width: 100%; border-collapse: collapse; font-size: 13px; }
        th, td { border-bottom: 1px solid var(--line); padding: 8px; text-align: left; vertical-align: top; }
        th { background: #faf9f8; font-weight: 600; }
        label { display: block; font-size: 12px; color: var(--muted); margin-bottom: 4px; }
        input, select, textarea { width: 100%; padding: 8px; border: 1px solid #c8c6c4; border-radius: 4px; background: #fff; }
        .tabs { display: flex; border-bottom: 1px solid var(--line); margin-bottom: 10px; gap: 8px; overflow: auto; }
        .tab-btn { border: 0; background: transparent; border-bottom: 2px solid transparent; padding: 8px 6px; cursor: pointer; color: var(--muted); white-space: nowrap; }
        .tab-btn.active { color: var(--brand-dark); border-color: var(--brand-dark); font-weight: 600; }
        .tab { display: none; }
        .tab.active { display: block; }
        .flash { padding: 10px; border-radius: 6px; margin-bottom: 10px; font-size: 13px; }
        .flash.ok { background: #dff6dd; color: #0f5132; }
        .flash.err { background: #fde7e9; color: #842029; }
        .kpi { background: #fff; border: 1px solid var(--line); border-radius: 8px; padding: 10px; }
        .kpi .v { font-size: 24px; font-weight: 700; }
        .muted { color: var(--muted); font-size: 12px; }
        @media (max-width: 980px) {
            .wrap { grid-template-columns: 1fr; }
            .nav { border-right: 0; border-bottom: 1px solid var(--line); }
            .grid.cols-2, .grid.cols-4 { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<?php $currentLocale = service('request')->getLocale(); ?>
<?php $redirectUrl = current_url(); ?>
<?php $currentUserName = (string) (session('current_user_name') ?? session('current_username') ?? '-'); ?>
<div class="top">
    <a class="top-config" href="<?= site_url('settings') ?>"><?= esc(t('settings')) ?></a>
    <div class="lang-switch">
        <span><?= esc(t('language')) ?>:</span>
        <a class="<?= $currentLocale === 'es' ? 'active' : '' ?>" href="<?= site_url('lang/es?redirect=' . urlencode($redirectUrl)) ?>"><?= esc(t('lang_es')) ?></a>
        <a class="<?= $currentLocale === 'pt' ? 'active' : '' ?>" href="<?= site_url('lang/pt?redirect=' . urlencode($redirectUrl)) ?>"><?= esc(t('lang_pt')) ?></a>
        <a class="<?= $currentLocale === 'en' ? 'active' : '' ?>" href="<?= site_url('lang/en?redirect=' . urlencode($redirectUrl)) ?>"><?= esc(t('lang_en')) ?></a>
        <a class="<?= $currentLocale === 'it' ? 'active' : '' ?>" href="<?= site_url('lang/it?redirect=' . urlencode($redirectUrl)) ?>"><?= esc(t('lang_it')) ?></a>
    </div>
    <b><?= esc(t('app_name')) ?></b> | <?= esc(t('style_label')) ?> | <?= esc(t('user')) ?>: <?= esc($currentUserName) ?> | <?= esc(t('area')) ?>: <?= esc($area ?? t('general')) ?>
</div>
<div class="wrap">
    <aside class="nav">
        <h3><?= esc(t('sitemap')) ?></h3>
        <a class="<?= ($area ?? '') === t('tickets') ? 'active' : '' ?>" href="<?= site_url('tickets') ?>"><?= esc(t('tickets')) ?></a>
        <a class="<?= ($area ?? '') === t('assets') ? 'active' : '' ?>" href="<?= site_url('assets') ?>"><?= esc(t('assets')) ?></a>
        <a class="<?= ($area ?? '') === t('customers') ? 'active' : '' ?>" href="<?= site_url('customers') ?>"><?= esc(t('customers')) ?></a>
        <a class="<?= ($area ?? '') === t('master_data') ? 'active' : '' ?>" href="<?= site_url('master-data') ?>"><?= esc(t('master_data')) ?></a>
        <a class="<?= ($area ?? '') === t('reports') ? 'active' : '' ?>" href="<?= site_url('dashboard') ?>"><?= esc(t('reports')) ?></a>
        <a class="<?= ($area ?? '') === t('admin') ? 'active' : '' ?>" href="<?= site_url('admin/config') ?>"><?= esc(t('admin')) ?></a>
        <a class="<?= ($area ?? '') === t('settings') ? 'active' : '' ?>" href="<?= site_url('settings') ?>"><?= esc(t('settings')) ?></a>
    </aside>
    <main class="main">
        <div class="command">
            <?php foreach (($commandActions ?? []) as $i => $a): ?>
                <a class="btn <?= $i === 0 ? 'primary' : '' ?>" href="<?= esc($a['href']) ?>"><?= esc($a['label']) ?></a>
            <?php endforeach; ?>
        </div>

        <?php if (session()->getFlashdata('success')): ?>
            <div class="flash ok"><?= esc(session()->getFlashdata('success')) ?></div>
        <?php endif; ?>
        <?php if (session()->getFlashdata('error')): ?>
            <div class="flash err"><?= esc(session()->getFlashdata('error')) ?></div>
        <?php endif; ?>

        <?= $content ?? '' ?>
    </main>
</div>
<script>
document.querySelectorAll('[data-tab]').forEach(function (btn) {
    btn.addEventListener('click', function () {
        const root = btn.closest('[data-tabs-root]') || document;
        root.querySelectorAll('.tab-btn').forEach(function (x) { x.classList.remove('active'); });
        root.querySelectorAll('.tab').forEach(function (x) { x.classList.remove('active'); });
        btn.classList.add('active');
        const pane = root.querySelector('#' + btn.dataset.tab);
        if (pane) pane.classList.add('active');
    });
});
</script>
</body>
</html>
