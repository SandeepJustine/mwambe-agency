<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error" role="alert">
        <p><strong>Please fix the following:</strong></p>
        <ul>
            <?php foreach ($errors as $msg): ?>
                <li><?= e($msg) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="/admin/homepage/slides/<?= (int) $slide['id'] ?>/edit"
      enctype="multipart/form-data" class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <fieldset class="form__group">
        <legend>Content</legend>

        <div class="form__row">
            <label for="eyebrow" class="form__label">Eyebrow <span class="form__hint">optional</span></label>
            <input type="text" id="eyebrow" name="eyebrow" class="form__input"
                   value="<?= e($slide['eyebrow'] ?? '') ?>" maxlength="120">
        </div>

        <div class="form__row">
            <label for="headline" class="form__label">Headline <span class="form__required" aria-hidden="true">*</span></label>
            <input type="text" id="headline" name="headline" class="form__input" required
                   value="<?= e($slide['headline']) ?>" maxlength="255"
                   aria-invalid="<?= isset($errors['headline']) ? 'true' : 'false' ?>">
        </div>

        <div class="form__row">
            <label for="supporting" class="form__label">Supporting text <span class="form__required" aria-hidden="true">*</span></label>
            <textarea id="supporting" name="supporting" class="form__textarea" rows="4" required
                      aria-invalid="<?= isset($errors['supporting']) ? 'true' : 'false' ?>"><?= e($slide['supporting']) ?></textarea>
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>Buttons</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="primary_cta_label" class="form__label">Primary button label</label>
                <input type="text" id="primary_cta_label" name="primary_cta_label" class="form__input"
                       value="<?= e($slide['primary_cta_label'] ?? '') ?>" maxlength="60">
            </div>
            <div class="form__row">
                <label for="primary_cta_url" class="form__label">Primary button URL</label>
                <input type="text" id="primary_cta_url" name="primary_cta_url" class="form__input"
                       value="<?= e($slide['primary_cta_url'] ?? '') ?>" maxlength="255" placeholder="/our-work">
            </div>
            <div class="form__row">
                <label for="secondary_cta_label" class="form__label">Secondary button label</label>
                <input type="text" id="secondary_cta_label" name="secondary_cta_label" class="form__input"
                       value="<?= e($slide['secondary_cta_label'] ?? '') ?>" maxlength="60">
            </div>
            <div class="form__row">
                <label for="secondary_cta_url" class="form__label">Secondary button URL</label>
                <input type="text" id="secondary_cta_url" name="secondary_cta_url" class="form__input"
                       value="<?= e($slide['secondary_cta_url'] ?? '') ?>" maxlength="255" placeholder="/partner-with-us">
            </div>
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>Appearance</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="layout" class="form__label">Layout variant</label>
                <select id="layout" name="layout" class="form__select">
                    <?php foreach (['editorial' => 'Editorial (slide 1 style)', 'split' => 'Split (slide 2 style)', 'image_led' => 'Image-led (slide 3 style)'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $slide['layout'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__row">
                <label for="workflow_status" class="form__label">Status</label>
                <select id="workflow_status" name="workflow_status" class="form__select">
                    <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $slide['workflow_status'] === $value ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__row">
            <label for="image" class="form__label">Slide image <span class="form__hint">JPEG, PNG or WebP, max 5 MB</span></label>

            <?php if (!empty($slide['media_filename'])): ?>
                <div class="form__preview">
                    <img src="<?= e(asset('uploads/' . $slide['media_filename'])) ?>" alt="<?= e($slide['media_alt'] ?? '') ?>">
                    <p class="form__preview-meta">Currently: <code><?= e($slide['media_filename']) ?></code></p>
                </div>
            <?php endif; ?>

            <input type="file" id="image" name="image" class="form__input" accept="image/jpeg,image/png,image/webp">
        </div>

        <div class="form__row">
            <label for="image_alt" class="form__label">Image alternative text <span class="form__hint">describe the image for screen readers</span></label>
            <input type="text" id="image_alt" name="image_alt" class="form__input"
                   value="<?= e($slide['media_alt'] ?? '') ?>" maxlength="250">
        </div>
    </fieldset>

    <div class="form__actions">
        <a href="/admin/homepage/slides" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save changes</button>
    </div>
</form>