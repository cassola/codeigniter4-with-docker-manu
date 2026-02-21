<h1 class="title"><?= esc(t('repair_tickets')) ?></h1>
<div class="card">
    <form method="get" class="grid cols-2">
        <div>
            <label><?= esc(t('status')) ?></label>
            <select name="status">
                <option value=""><?= esc(t('all')) ?></option>
                <?php foreach ($statusChoices as $status): ?>
                    <option value="<?= esc($status) ?>" <?= $statusFilter === $status ? 'selected' : '' ?>><?= esc(status_label($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label><?= esc(t('current_site')) ?></label>
            <select name="site_id">
                <option value=""><?= esc(t('all')) ?></option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?= esc($site['id']) ?>" <?= $siteFilter === (int) $site['id'] ? 'selected' : '' ?>>
                        <?= esc($site['rm_sitecode']) ?> - <?= esc($site['rm_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div><button class="btn primary" type="submit"><?= esc(t('apply_filters')) ?></button></div>
    </form>
</div>

<div class="card">
    <table>
        <thead>
        <tr>
            <th><?= esc(t('sap_notice')) ?></th>
            <th><?= esc(t('intake')) ?></th>
            <th><?= esc(t('current_site')) ?></th>
            <th><?= esc(t('asset')) ?></th>
            <th><?= esc(t('customer_plant')) ?></th>
            <th><?= esc(t('status')) ?></th>
            <th><?= esc(t('priority')) ?></th>
            <th><?= esc(t('action')) ?></th>
        </tr>
        </thead>
        <tbody>
        <?php foreach ($tickets as $t): ?>
            <tr>
                <td><?= esc($t['rm_sapnoticenumber']) ?></td>
                <td><?= esc($t['intake_site']) ?></td>
                <td><?= esc($t['processing_site']) ?></td>
                <td><?= esc($t['rm_serialnumber']) ?></td>
                <td><?= esc($t['customer_plant']) ?></td>
                <td><?= esc(status_label($t['rm_status'])) ?></td>
                <td><?= esc(priority_label($t['rm_priority'])) ?></td>
                <td><a class="btn" href="<?= site_url('tickets/' . $t['id']) ?>"><?= esc(t('open')) ?></a></td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
