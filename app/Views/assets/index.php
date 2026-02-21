<h1 class="title"><?= esc(t('assets')) ?></h1>
<div class="card">
    <table>
        <thead><tr><th><?= esc(t('serial')) ?></th><th><?= esc(t('model')) ?></th><th><?= esc(t('status')) ?></th><th><?= esc(t('current_site')) ?></th><th><?= esc(t('owner_type')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($assets as $a): ?>
            <tr>
                <td><?= esc($a['rm_serialnumber']) ?></td>
                <td><?= esc($a['rm_modelcode']) ?></td>
                <td><?= esc(status_label($a['rm_status'])) ?></td>
                <td><?= esc($a['current_site'] ?? '-') ?></td>
                <td><?= esc($a['rm_currentownertype']) ?></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
