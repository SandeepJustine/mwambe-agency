<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert--error" role="alert">
        <p><strong>Please fix:</strong></p>
        <ul><?php foreach ($errors as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post"
      action="<?= !empty($sector['id']) ? '/admin/sectors/' . (int) $sector['id'] . '/edit' : '/admin/sectors' ?>"
      enctype="multipart/form-data" class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <fieldset class="form__group">
        <legend>Content</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="name" class="form__label">Name <span class="form__required">*</span></label>
                <input type="text" id="name" name="name" class="form__input" required
                       value="<?= e($sector['name']) ?>" maxlength="190">
            </div>
            <div class="form__row">
                <label for="slug" class="form__label">Slug <span class="form__hint">a-z, 0-9, hyphens</span></label>
                <input type="text" id="slug" name="slug" class="form__input"
                       value="<?= e($sector['slug']) ?>" maxlength="120">
            </div>
        </div>

        <div class="form__row">
            <label for="summary" class="form__label">Summary</label>
            <textarea id="summary" name="summary" class="form__textarea" rows="2" maxlength="500"><?= e($sector['summary'] ?? '') ?></textarea>
        </div>

        <div class="form__row">
            <label for="body" class="form__label">Body</label>
            <textarea id="body" name="body" class="form__textarea" rows="8"><?= e($sector['body'] ?? '') ?></textarea>
        </div>

        <div class="form__grid">
            <div class="form__row">
                <label for="icon_key" class="form__label">Icon key <span class="form__hint">optional</span></label>
                <input type="text" id="icon_key" name="icon_key" class="form__input"
                       value="<?= e($sector['icon_key'] ?? '') ?>" maxlength="40">
            </div>
            <div class="form__row">
                <label for="position" class="form__label">Display order</label>
                <input type="number" id="position" name="position" class="form__input"
                       value="<?= (int) ($sector['position'] ?? 0) ?>" min="0" max="999">
            </div>
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>Publishing</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="workflow_status" class="form__label">Status</label>
                <select id="workflow_status" name="workflow_status" class="form__select">
                    <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $v => $lbl): ?>
                        <option value="<?= e($v) ?>" <?= $sector['workflow_status'] === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__row form__row--check">
                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                       <?= !empty($sector['is_featured']) ? 'checked' : '' ?>>
                <label for="is_featured">Featured</label>
            </div>
        </div>

        <div class="form__row">
            <label for="image" class="form__label">Image</label>
            <?php if (!empty($sector['media_filename'])): ?>
                <div class="form__preview">
                    <img src="<?= e(asset('uploads/' . $sector['media_filename'])) ?>" alt="">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form__input" accept="image/jpeg,image/png,image/webp">
        </div>
    </fieldset>

    <div class="form__actions">
        <a href="/admin/sectors" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save</button>
    </div>
</form>

<?php if (!empty($sector['id'])): ?>
<div class="admin__panel">
    <h2>Danger zone</h2>
    <form method="post" action="/admin/sectors/<?= (int) $sector['id'] ?>/delete"
          onsubmit="return confirm('Delete this sector permanently?');">
        <?= $csrf->field() ?>
        <button type="submit" class="btn btn--sm">Delete sector</button>
    </form>
</div>
<?php endif; ?>