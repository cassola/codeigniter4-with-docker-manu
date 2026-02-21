<h1 class="title"><?= esc(t('customers_title')) ?></h1>
<div class="grid cols-2">
    <div class="card">
        <h3><?= esc(t('customers_list')) ?></h3>
        <table>
            <thead><tr><th><?= esc(t('code')) ?></th><th><?= esc(t('name')) ?></th><th><?= esc(t('trade_name')) ?></th><th><?= esc(t('plants')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($customers as $c): ?>
                <tr>
                    <td><?= esc($c['rm_customercode']) ?></td>
                    <td><?= esc($c['rm_legalname']) ?></td>
                    <td><?= esc($c['rm_tradename'] ?? '-') ?></td>
                    <td><?= esc((string) $c['plants']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3><?= esc(t('customer_plants')) ?></h3>
        <table>
            <thead><tr><th><?= esc(t('plant_code')) ?></th><th><?= esc(t('plant_name')) ?></th><th><?= esc(t('customers')) ?></th></tr></thead>
            <tbody>
            <?php foreach ($plants as $p): ?>
                <tr>
                    <td><?= esc($p['rm_plantcode']) ?></td>
                    <td><?= esc($p['rm_name']) ?></td>
                    <td><?= esc($p['rm_legalname']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
