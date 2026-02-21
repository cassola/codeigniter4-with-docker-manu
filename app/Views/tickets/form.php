<h1 class="title"><?= esc(t('new_repair_ticket')) ?></h1>
<div class="card">
    <?php $defaultSiteId = (int) (session('default_site_id') ?? 0); ?>
    <?php $oldIntakeSite = old('rm_intakesite_id'); ?>
    <form id="ticket-form" method="post" action="<?= site_url('tickets') ?>" class="grid cols-2">
        <?= csrf_field() ?>

        <div>
            <label><?= esc(t('intake_site_required')) ?></label>
            <select name="rm_intakesite_id">
                <option value=""><?= esc(t('select_site')) ?></option>
                <?php foreach ($sites as $s): ?>
                    <option value="<?= esc($s['id']) ?>" <?= ((string) $oldIntakeSite !== '' ? $oldIntakeSite == $s['id'] : $defaultSiteId === (int) $s['id']) ? 'selected' : '' ?>><?= esc($s['rm_sitecode']) ?> - <?= esc($s['rm_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label><?= esc(t('sap_required')) ?></label>
            <input type="text" name="rm_sapnoticenumber" value="<?= esc(old('rm_sapnoticenumber')) ?>" required>
        </div>

        <div>
            <label><?= esc(t('customer_plant_required')) ?></label>
            <select name="rm_customerplant_id" required>
                <option value=""><?= esc(t('select_plant')) ?></option>
                <?php foreach ($plants as $p): ?>
                    <option value="<?= esc($p['id']) ?>" <?= old('rm_customerplant_id') == $p['id'] ? 'selected' : '' ?>><?= esc($p['rm_plantcode']) ?> - <?= esc($p['rm_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label><?= esc(t('asset_required')) ?></label>
            <select name="rm_asset_id" required>
                <option value=""><?= esc(t('select_asset')) ?></option>
                <?php foreach ($assets as $a): ?>
                    <option value="<?= esc($a['id']) ?>" <?= old('rm_asset_id') == $a['id'] ? 'selected' : '' ?>><?= esc($a['rm_serialnumber']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label><?= esc(t('created_by')) ?></label>
            <select name="rm_createdby_id">
                <option value=""><?= esc(t('auto_default')) ?></option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= esc($u['id']) ?>" <?= old('rm_createdby_id') == $u['id'] ? 'selected' : '' ?>><?= esc($u['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label><?= esc(t('assigned_to')) ?></label>
            <select name="rm_assignedto_id">
                <option value=""><?= esc(t('unassigned')) ?></option>
                <?php foreach ($users as $u): ?>
                    <option value="<?= esc($u['id']) ?>" <?= old('rm_assignedto_id') == $u['id'] ? 'selected' : '' ?>><?= esc($u['full_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div>
            <label><?= esc(t('status')) ?></label>
            <select name="rm_status">
                <option value=""><?= esc(t('default_by_site')) ?></option>
                <?php foreach ($statusChoices as $status): ?>
                    <option value="<?= esc($status) ?>" <?= old('rm_status') === $status ? 'selected' : '' ?>><?= esc(status_label($status)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label><?= esc(t('priority')) ?></label>
            <select name="rm_priority">
                <option value=""><?= esc(t('default_by_site')) ?></option>
                <?php foreach ($priorityChoices as $priority): ?>
                    <option value="<?= esc($priority) ?>" <?= old('rm_priority') === $priority ? 'selected' : '' ?>><?= esc(priority_label($priority)) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="grid" style="grid-column: 1 / -1;">
            <div>
                <label><?= esc(t('reported_failure_required')) ?></label>
                <textarea name="rm_reportedfailure" rows="4" required><?= esc(old('rm_reportedfailure')) ?></textarea>
            </div>
            <div>
                <label><?= esc(t('detected_failure')) ?></label>
                <textarea name="rm_detectedfailure" rows="3"><?= esc(old('rm_detectedfailure')) ?></textarea>
            </div>
            <div>
                <label><?= esc(t('repair_solution')) ?></label>
                <textarea name="rm_repairsln" rows="3"><?= esc(old('rm_repairsln')) ?></textarea>
            </div>
        </div>

        <div><label><input type="checkbox" name="rm_iswarranty" value="1" <?= old('rm_iswarranty') ? 'checked' : '' ?>> <?= esc(t('warranty')) ?></label></div>
        <div><label><input type="checkbox" name="rm_isnonrepairable" value="1" <?= old('rm_isnonrepairable') ? 'checked' : '' ?>> <?= esc(t('non_repairable')) ?></label></div>
        <div><label><input type="checkbox" name="rm_replacementgiven" value="1" <?= old('rm_replacementgiven') ? 'checked' : '' ?>> <?= esc(t('replacement_given')) ?></label></div>

        <div style="grid-column:1/-1;"><button type="submit" class="btn primary"><?= esc(t('create_ticket')) ?></button></div>
    </form>
</div>
