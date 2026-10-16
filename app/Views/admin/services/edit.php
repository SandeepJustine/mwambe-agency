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
      action="<?= !empty($service['id']) ? '/admin/services/' . (int) $service['id'] . '/edit' : '/admin/services' ?>"
      enctype="multipart/form-data" class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <fieldset class="form__group">
        <legend>Content</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="name" class="form__label">Name <span class="form__required">*</span></label>
                <input type="text" id="name" name="name" class="form__input" required
                       value="<?= e($service['name']) ?>" maxlength="190">
            </div>
            <div class="form__row">
                <label for="slug" class="form__label">Slug</label>
                <input type="text" id="slug" name="slug" class="form__input"
                       value="<?= e($service['slug']) ?>" maxlength="120">
            </div>
            <div class="form__row">
                <label for="category" class="form__label">Category <span class="form__hint">e.g. youth, enterprise, agriculture, community</span></label>
                <input type="text" id="category" name="category" class="form__input"
                       value="<?= e($service['category'] ?? '') ?>" maxlength="60">
            </div>
            <div class="form__row">
                <label for="icon_key" class="form__label">Icon key</label>
                <input type="text" id="icon_key" name="icon_key" class="form__input"
                       value="<?= e($service['icon_key'] ?? '') ?>" maxlength="40">
            </div>
        </div>

        <div class="form__row">
            <label for="summary" class="form__label">Summary</label>
            <textarea id="summary" name="summary" class="form__textarea" rows="2" maxlength="500"><?= e($service['summary'] ?? '') ?></textarea>
        </div>

        <div class="form__row">
            <label for="body" class="form__label">Body</label>
            <textarea id="body" name="body" class="form__textarea" rows="8"><?= e($service['body'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>Publishing</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="position" class="form__label">Display order</label>
                <input type="number" id="position" name="position" class="form__input"
                       value="<?= (int) ($service['position'] ?? 0) ?>" min="0" max="999">
            </div>
            <div class="form__row">
                <label for="workflow_status" class="form__label">Status</label>
                <select id="workflow_status" name="workflow_status" class="form__select">
                    <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $v => $lbl): ?>
                        <option value="<?= e($v) ?>" <?= $service['workflow_status'] === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__row form__row--check">
            <input type="checkbox" id="is_featured" name="is_featured" value="1"
                   <?= !empty($service['is_featured']) ? 'checked' : '' ?>>
            <label for="is_featured">Featured on homepage and Our Work page</label>
        </div>

        <div class="form__row">
            <label for="image" class="form__label">Image</label>
            <?php if (!empty($service['media_filename'])): ?>
                <div class="form__preview">
                    <img src="<?= e(asset('uploads/' . $service['media_filename'])) ?>" alt="">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form__input" accept="image/jpeg,image/png,image/webp">
        </div>
    </fieldset>

    <div class="form__actions">
        <a href="/admin/services" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save</button>
    </div>
</form>

<?php if (!empty($service['id'])): ?>
<div class="admin__panel">
    <h2>Danger zone</h2>
    <form method="post" action="/admin/services/<?= (int) $service['id'] ?>/delete"
          onsubmit="return confirm('Delete this service permanently?');">
        <?= $csrf->field() ?>
        <button type="submit" class="btn btn--sm">Delete service</button>
    </form>
</div>
<?php endif; ?>