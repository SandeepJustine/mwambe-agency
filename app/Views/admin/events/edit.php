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
      action="<?= !empty($event['id']) ? '/admin/events/' . (int) $event['id'] . '/edit' : '/admin/events' ?>"
      enctype="multipart/form-data" class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <fieldset class="form__group">
        <legend>Content</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="title" class="form__label">Title <span class="form__required">*</span></label>
                <input type="text" id="title" name="title" class="form__input" required
                       value="<?= e($event['title']) ?>" maxlength="190"
                       aria-invalid="<?= isset($errors['title']) ? 'true' : 'false' ?>">
                <?php if (isset($errors['title'])): ?><p class="form__error"><?= e($errors['title']) ?></p><?php endif; ?>
            </div>
            <div class="form__row">
                <label for="slug" class="form__label">Slug <span class="form__hint">a-z, 0-9, hyphens</span></label>
                <input type="text" id="slug" name="slug" class="form__input"
                       value="<?= e($event['slug']) ?>" maxlength="160">
                <?php if (isset($errors['slug'])): ?><p class="form__error"><?= e($errors['slug']) ?></p><?php endif; ?>
            </div>
        </div>

        <div class="form__row">
            <label for="summary" class="form__label">Summary</label>
            <textarea id="summary" name="summary" class="form__textarea" rows="2" maxlength="500"><?= e($event['summary'] ?? '') ?></textarea>
        </div>

        <div class="form__row">
            <label for="body" class="form__label">Body</label>
            <textarea id="body" name="body" class="form__textarea" rows="8"><?= e($event['body'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>Date &amp; Location</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="starts_at" class="form__label">Starts <span class="form__required">*</span></label>
                <input type="datetime-local" id="starts_at" name="starts_at" class="form__input" required
                       value="<?= e(date('Y-m-d\TH:i', strtotime($event['starts_at']))) ?>">
                <?php if (isset($errors['starts_at'])): ?><p class="form__error"><?= e($errors['starts_at']) ?></p><?php endif; ?>
            </div>
            <div class="form__row">
                <label for="ends_at" class="form__label">Ends</label>
                <input type="datetime-local" id="ends_at" name="ends_at" class="form__input"
                       value="<?= $event['ends_at'] ? e(date('Y-m-d\TH:i', strtotime($event['ends_at']))) : '' ?>">
            </div>
            <div class="form__row">
                <label for="location" class="form__label">Location</label>
                <input type="text" id="location" name="location" class="form__input"
                       value="<?= e($event['location'] ?? '') ?>" maxlength="190">
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
                        <option value="<?= e($v) ?>" <?= $event['workflow_status'] === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form__row form__row--check">
                <input type="checkbox" id="is_featured" name="is_featured" value="1"
                       <?= !empty($event['is_featured']) ? 'checked' : '' ?>">
                <label for="is_featured">Featured</label>
            </div>
        </div>

        <div class="form__row">
            <label for="image" class="form__label">Image</label>
            <?php if (!empty($event['media_filename'])): ?>
                <div class="form__preview">
                    <img src="<?= e(asset('uploads/' . $event['media_filename'])) ?>" alt="">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form__input" accept="image/jpeg,image/png,image/webp">
        </div>
    </fieldset>

    <div class="form__actions">
        <a href="/admin/events" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save</button>
    </div>
</form>

<?php if (!empty($event['id'])): ?>
    <div class="admin__panel">
        <h2>Danger zone</h2>
        <form method="post" action="/admin/events/<?= (int) $event['id'] ?>/delete"
            onsubmit="return confirm('Delete this event permanently?');">
            <?= $csrf->field() ?>
            <button type="submit" class="btn btn--sm">Delete event</button>
        </form>
    </div>
<?php endif; ?>