<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert--error" role="alert">
        <p><strong>Please fix:</strong></p>
        <ul><?php foreach ($errors as $message): ?><li><?= e($message) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post"
      action="<?= !empty($member['id']) ? '/admin/team/' . (int) $member['id'] . '/edit' : '/admin/team' ?>"
      enctype="multipart/form-data"
      class="form form--admin">
    <?= $csrf->field() ?>

    <fieldset class="form__group">
        <legend>Team member</legend>
        <div class="form__grid">
            <div class="form__row">
                <label for="name" class="form__label">Name <span class="form__required">*</span></label>
                <input type="text" id="name" name="name" class="form__input" value="<?= e($member['name']) ?>" maxlength="190" required>
            </div>
            <div class="form__row">
                <label for="position_title" class="form__label">Position <span class="form__required">*</span></label>
                <input type="text" id="position_title" name="position_title" class="form__input" value="<?= e($member['position_title']) ?>" maxlength="190" required>
            </div>
        </div>

        <div class="form__row">
            <label for="bio" class="form__label">Biography <span class="form__hint">optional</span></label>
            <textarea id="bio" name="bio" class="form__textarea" rows="5"><?= e($member['bio'] ?? '') ?></textarea>
        </div>

        <div class="form__grid">
            <div class="form__row">
                <label for="position" class="form__label">Display order</label>
                <input type="number" id="position" name="position" class="form__input" value="<?= (int) $member['position'] ?>" min="0" max="999">
            </div>
            <div class="form__row form__row--check">
                <input type="checkbox" id="is_active" name="is_active" value="1" <?= !empty($member['is_active']) ? 'checked' : '' ?>>
                <label for="is_active">Show on the public About page</label>
            </div>
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>Profile picture</legend>
        <?php if (!empty($member['media_filename'])): ?>
            <div class="form__preview">
                <img src="<?= e(asset('uploads/' . $member['media_filename'])) ?>" alt="<?= e($member['media_alt'] ?? '') ?>">
            </div>
        <?php endif; ?>
        <div class="form__row">
            <label for="image" class="form__label">Photo <?= empty($member['id']) ? '<span class="form__required">*</span>' : '' ?></label>
            <input type="file" id="image" name="image" class="form__input" accept="image/jpeg,image/png,image/webp" <?= empty($member['id']) ? 'required' : '' ?>>
            <p class="form__hint">JPEG, PNG or WebP. Leave empty to keep the current photo.</p>
        </div>
        <div class="form__row">
            <label for="image_alt" class="form__label">Image description <span class="form__hint">optional; defaults to the member's name</span></label>
            <input type="text" id="image_alt" name="image_alt" class="form__input" value="<?= e($member['media_alt'] ?? '') ?>" maxlength="250">
        </div>
    </fieldset>

    <div class="form__actions">
        <a href="/admin/team" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save team member</button>
    </div>
</form>

<?php if (!empty($member['id'])): ?>
    <div class="admin__panel">
        <h2>Remove team member</h2>
        <p>Removing this record will also remove the member from the public About page.</p>
        <form method="post" action="/admin/team/<?= (int) $member['id'] ?>/delete"
              onsubmit="return confirm('Remove this team member?');">
            <?= $csrf->field() ?>
            <button type="submit" class="btn btn--sm">Remove team member</button>
        </form>
    </div>
<?php endif; ?>
