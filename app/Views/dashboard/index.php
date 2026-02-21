<h1 class="title"><?= esc(t('repair_dashboard')) ?></h1>
<div class="grid cols-4">
    <div class="kpi"><div class="muted"><?= esc(t('total_tickets')) ?></div><div class="v"><?= esc((string) $kpis['totalTickets']) ?></div></div>
    <div class="kpi"><div class="muted"><?= esc(t('open_tickets_kpi')) ?></div><div class="v"><?= esc((string) $kpis['openTickets']) ?></div></div>
    <div class="kpi"><div class="muted"><?= esc(t('assets_in_repair')) ?></div><div class="v"><?= esc((string) $kpis['assetsInRepair']) ?></div></div>
    <div class="kpi"><div class="muted"><?= esc(t('materials_usage')) ?></div><div class="v"><?= esc((string) $kpis['materialsUsed']) ?></div></div>
</div>

<div class="grid cols-2" style="margin-top:12px;">
    <div class="card">
        <h3><?= esc(t('tickets_by_status')) ?></h3>
        <table>
            <thead><tr><th><?= esc(t('status')) ?></th><th><?= esc(t('total')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($statusData as $row): ?>
                <tr><td><?= esc(status_label($row['rm_status'])) ?></td><td><?= esc((string) $row['total']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3><?= esc(t('tickets_by_site')) ?></h3>
        <table>
            <thead><tr><th><?= esc(t('site')) ?></th><th><?= esc(t('name')) ?></th><th><?= esc(t('total')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($siteData as $row): ?>
                <tr>
                    <td><?= esc($row['rm_sitecode']) ?></td>
                    <td><?= esc($row['rm_name']) ?></td>
                    <td><?= esc((string) $row['total']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
