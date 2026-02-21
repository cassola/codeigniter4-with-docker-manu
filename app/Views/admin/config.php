<h1 class="title"><?= esc(t('admin_site_config')) ?></h1>
<div class="card">
    <table>
        <thead>
        <tr>
            <th><?= esc(t('site')) ?></th>
            <th><?= esc(t('default_priority')) ?></th>
            <th><?= esc(t('default_status')) ?></th>
            <th><?= esc(t('close_requires_solution')) ?></th>
            <th><?= esc(t('require_test_report_on_close')) ?></th>
            <th><?= esc(t('checklist_template')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($configs as $cfg): ?>
            <tr>
                <td><?= esc($cfg['rm_sitecode']) ?> - <?= esc($cfg['rm_name']) ?></td>
                <td><?= esc(priority_label($cfg['rm_defaultpriority'])) ?></td>
                <td><?= esc(status_label($cfg['rm_defaultticketstatus'])) ?></td>
                <td><?= esc(yes_no_label((int) $cfg['rm_close_requires_solution'] === 1)) ?></td>
                <td><?= esc(yes_no_label((int) $cfg['rm_require_testreport_on_close'] === 1)) ?></td>
                <td><?= esc($cfg['rm_default_test_checklist'] ?? '') ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
