<h1 class="title"><?= esc(t('master_data_title')) ?></h1>
<div class="grid cols-2">
    <div class="card">
        <h3><?= esc(t('sites')) ?></h3>
        <table>
            <thead><tr><th><?= esc(t('code')) ?></th><th><?= esc(t('name')) ?></th><th><?= esc(t('country')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($sites as $site): ?>
                <tr>
                    <td><?= esc($site['rm_sitecode']) ?></td>
                    <td><?= esc($site['rm_name']) ?></td>
                    <td><?= esc($site['rm_country']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3><?= esc(t('product_models')) ?></h3>
        <table>
            <thead><tr><th><?= esc(t('code')) ?></th><th><?= esc(t('description')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($models as $m): ?>
                <tr><td><?= esc($m['rm_modelcode']) ?></td><td><?= esc($m['rm_description']) ?></td></tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card">
    <h3><?= esc(t('materials')) ?></h3>
    <table>
        <thead><tr><th><?= esc(t('part_number')) ?></th><th><?= esc(t('description')) ?></th><th><?= esc(t('unit')) ?></th></tr></thead>
        <tbody>
        <?php foreach ($materials as $m): ?>
            <tr><td><?= esc($m['rm_partnumber']) ?></td><td><?= esc($m['rm_description']) ?></td><td><?= esc($m['rm_unit']) ?></td></tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
