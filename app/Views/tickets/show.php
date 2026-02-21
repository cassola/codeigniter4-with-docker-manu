<h1 class="title"><?= esc(t('ticket')) ?> <?= esc($ticket['rm_sapnoticenumber']) ?></h1>
<div class="card" data-tabs-root>
    <div class="tabs">
        <button class="tab-btn active" type="button" data-tab="tab-intake"><?= esc(t('intake')) ?></button>
        <button class="tab-btn" type="button" data-tab="tab-diagnosis"><?= esc(t('diagnosis')) ?></button>
        <button class="tab-btn" type="button" data-tab="tab-repair"><?= esc(t('repair')) ?></button>
        <button class="tab-btn" type="button" data-tab="tab-logistics"><?= esc(t('logistics')) ?></button>
        <button class="tab-btn" type="button" data-tab="tab-history"><?= esc(t('history')) ?></button>
    </div>

    <div id="tab-intake" class="tab active">
        <div class="grid cols-2">
            <div><b><?= esc(t('intake_site')) ?>:</b> <?= esc($ticket['intake_site']) ?></div>
            <div><b><?= esc(t('current_processing_site')) ?>:</b> <?= esc($ticket['processing_site']) ?></div>
            <div><b><?= esc(t('asset')) ?>:</b> <?= esc($ticket['rm_serialnumber']) ?> (<?= esc($ticket['asset_status']) ?>)</div>
            <div><b><?= esc(t('customer_plant')) ?>:</b> <?= esc($ticket['customer_plant']) ?></div>
            <div><b><?= esc(t('status')) ?>:</b> <?= esc(status_label($ticket['rm_status'])) ?></div>
            <div><b><?= esc(t('priority')) ?>:</b> <?= esc(priority_label($ticket['rm_priority'])) ?></div>
            <div style="grid-column:1/-1;"><b><?= esc(t('reported_failure')) ?>:</b><br><?= nl2br(esc($ticket['rm_reportedfailure'])) ?></div>
        </div>
    </div>

    <div id="tab-diagnosis" class="tab">
        <div class="grid cols-2">
            <div style="grid-column:1/-1;"><b><?= esc(t('detected_failure')) ?></b><br><?= nl2br(esc($ticket['rm_detectedfailure'] ?? '')) ?></div>
            <div>
                <form id="status-form" method="post" action="<?= site_url('tickets/' . $ticket['id'] . '/status') ?>" class="grid">
                    <?= csrf_field() ?>
                    <label><?= esc(t('change_status')) ?></label>
                    <select name="rm_status">
                        <?php foreach ($statusChoices as $status): ?>
                            <option value="<?= esc($status) ?>" <?= $ticket['rm_status'] === $status ? 'selected' : '' ?>><?= esc(status_label($status)) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label><?= esc(t('updated_by')) ?></label>
                    <select name="rm_changedby_id">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= esc($u['id']) ?>"><?= esc($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <label><?= esc(t('repair_solution_required_on_close')) ?></label>
                    <textarea name="rm_repairsln" rows="3"><?= esc($ticket['rm_repairsln'] ?? '') ?></textarea>
                    <label><?= esc(t('comment')) ?></label>
                    <textarea name="rm_comment" rows="2"></textarea>
                    <button type="submit" class="btn primary"><?= esc(t('apply_status')) ?></button>
                </form>
            </div>
        </div>
    </div>

    <div id="tab-repair" class="tab">
        <div id="material-form" class="card">
            <h3><?= esc(t('add_material')) ?></h3>
            <form method="post" action="<?= site_url('tickets/' . $ticket['id'] . '/materials') ?>" class="grid cols-2">
                <?= csrf_field() ?>
                <div>
                    <label><?= esc(t('material')) ?></label>
                    <select name="rm_material_id" required>
                        <option value=""><?= esc(t('select')) ?></option>
                        <?php foreach ($materialOptions as $m): ?>
                            <option value="<?= esc($m['id']) ?>"><?= esc($m['rm_partnumber']) ?> - <?= esc($m['rm_description']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label><?= esc(t('qty')) ?></label><input type="number" name="rm_qty" step="0.01" min="0.01" required></div>
                <div>
                    <label><?= esc(t('used_by')) ?></label>
                    <select name="rm_usedby_id">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= esc($u['id']) ?>"><?= esc($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div><label><?= esc(t('comment')) ?></label><input type="text" name="rm_comment"></div>
                <div style="grid-column:1/-1;"><button class="btn primary" type="submit"><?= esc(t('add_material_btn')) ?></button></div>
            </form>
        </div>

        <div class="card">
            <h3><?= esc(t('materials_subgrid')) ?></h3>
            <table>
                <thead><tr><th><?= esc(t('part')) ?></th><th><?= esc(t('description')) ?></th><th><?= esc(t('qty')) ?></th><th><?= esc(t('used_by')) ?></th><th><?= esc(t('used_at')) ?></th><th><?= esc(t('comment')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($materials as $m): ?>
                    <tr>
                        <td><?= esc($m['rm_partnumber']) ?></td>
                        <td><?= esc($m['rm_description']) ?></td>
                        <td><?= esc((string) $m['rm_qty']) ?></td>
                        <td><?= esc($m['used_by'] ?? '-') ?></td>
                        <td><?= esc($m['rm_usedat']) ?></td>
                        <td><?= esc($m['rm_comment'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-logistics" class="tab">
        <div id="transfer-form" class="card">
            <h3><?= esc(t('transfer_to_site')) ?></h3>
            <form method="post" action="<?= site_url('tickets/' . $ticket['id'] . '/transfer') ?>" class="grid cols-2">
                <?= csrf_field() ?>
                <div>
                    <label><?= esc(t('to_site')) ?></label>
                    <select name="to_site_id" required>
                        <option value=""><?= esc(t('select_destination')) ?></option>
                        <?php foreach ($sites as $s): ?>
                            <option value="<?= esc($s['id']) ?>"><?= esc($s['rm_sitecode']) ?> - <?= esc($s['rm_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label><?= esc(t('executed_by')) ?></label>
                    <select name="rm_changedby_id">
                        <?php foreach ($users as $u): ?>
                            <option value="<?= esc($u['id']) ?>"><?= esc($u['full_name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="grid-column:1/-1;">
                    <label><?= esc(t('notes')) ?></label>
                    <textarea name="rm_comment" rows="2"></textarea>
                </div>
                <div style="grid-column:1/-1;"><button class="btn primary" type="submit"><?= esc(t('transfer')) ?></button></div>
            </form>
        </div>

        <div class="card">
            <h3><?= esc(t('movements_subgrid')) ?></h3>
            <table>
                <thead><tr><th><?= esc(t('type')) ?></th><th><?= esc(t('from')) ?></th><th><?= esc(t('to')) ?></th><th><?= esc(t('date')) ?></th><th><?= esc(t('notes')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($movements as $mv): ?>
                    <tr>
                        <td><?= esc($mv['rm_movementtype']) ?></td>
                        <td><?= esc($mv['from_site']) ?></td>
                        <td><?= esc($mv['to_site']) ?></td>
                        <td><?= esc($mv['rm_movedat']) ?></td>
                        <td><?= esc($mv['rm_notes'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="tab-history" class="tab">
        <div class="card">
            <h3><?= esc(t('status_history_subgrid')) ?></h3>
            <table>
                <thead><tr><th><?= esc(t('from')) ?></th><th><?= esc(t('to')) ?></th><th><?= esc(t('changed_by')) ?></th><th><?= esc(t('date')) ?></th><th><?= esc(t('comment')) ?></th></tr></thead>
                <tbody>
                <?php foreach ($history as $h): ?>
                    <tr>
                        <td><?= esc(isset($h['rm_fromstatus']) ? status_label($h['rm_fromstatus']) : '-') ?></td>
                        <td><?= esc(status_label($h['rm_tostatus'])) ?></td>
                        <td><?= esc($h['changed_by'] ?? '-') ?></td>
                        <td><?= esc($h['rm_changedat']) ?></td>
                        <td><?= esc($h['rm_comment'] ?? '') ?></td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
