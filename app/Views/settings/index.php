<h1 class="title"><?= esc(t('settings_title')) ?></h1>
<div class="card">
    <?php $oldUserId = old('current_user_id'); ?>
    <?php $oldLocale = old('locale'); ?>
    <?php $oldSiteId = old('default_site_id'); ?>
    <div class="muted" style="margin-bottom:10px;">
        <?= esc(t('current_user')) ?>:
        <?= esc($currentUser['full_name'] ?? '-') ?>
        <?php if (! empty($currentUser['username'])): ?>
            (<?= esc($currentUser['username']) ?>)
        <?php endif; ?>
    </div>
    <form method="get" action="<?= site_url('settings') ?>" class="grid cols-2" style="margin-bottom:12px;">
        <div>
            <label><?= esc(t('user')) ?></label>
            <select name="current_user_id" onchange="this.form.submit()" required>
                <?php foreach ($users as $user): ?>
                    <option value="<?= esc($user['id']) ?>" <?= ((string) $oldUserId !== '' ? (int) $oldUserId === (int) $user['id'] : $currentUserId === (int) $user['id']) ? 'selected' : '' ?>>
                        <?= esc($user['full_name']) ?> (<?= esc($user['username']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
    </form>

    <form id="settings-form" method="post" action="<?= site_url('settings') ?>" class="grid cols-2">
        <?= csrf_field() ?>
        <input type="hidden" name="current_user_id" value="<?= esc((string) $currentUserId) ?>">

        <div>
            <label><?= esc(t('default_language')) ?></label>
            <select name="locale" required>
                <option value="es" <?= (($oldLocale !== null && $oldLocale !== '') ? $oldLocale === 'es' : $locale === 'es') ? 'selected' : '' ?>><?= esc(t('lang_es')) ?></option>
                <option value="pt" <?= (($oldLocale !== null && $oldLocale !== '') ? $oldLocale === 'pt' : $locale === 'pt') ? 'selected' : '' ?>><?= esc(t('lang_pt')) ?></option>
                <option value="en" <?= (($oldLocale !== null && $oldLocale !== '') ? $oldLocale === 'en' : $locale === 'en') ? 'selected' : '' ?>><?= esc(t('lang_en')) ?></option>
                <option value="it" <?= (($oldLocale !== null && $oldLocale !== '') ? $oldLocale === 'it' : $locale === 'it') ? 'selected' : '' ?>><?= esc(t('lang_it')) ?></option>
            </select>
        </div>

        <div>
            <label><?= esc(t('default_site')) ?></label>
            <select name="default_site_id">
                <option value="0" <?= ((string) $oldSiteId !== '' ? (int) $oldSiteId === 0 : $defaultSiteId === 0) ? 'selected' : '' ?>><?= esc(t('none')) ?></option>
                <?php foreach ($sites as $site): ?>
                    <option value="<?= esc($site['id']) ?>" <?= ((string) $oldSiteId !== '' ? (int) $oldSiteId === (int) $site['id'] : $defaultSiteId === (int) $site['id']) ? 'selected' : '' ?>>
                        <?= esc($site['rm_sitecode']) ?> - <?= esc($site['rm_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div style="grid-column:1/-1;">
            <button type="submit" class="btn primary"><?= esc(t('save_settings')) ?></button>
        </div>
    </form>
</div>
