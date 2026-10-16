<?php
/**
 * Admin programme edit / create view.
 *
 * Serves both create and edit. The `$programme` array is fully populated
 * in both cases — from empty defaults on create, from the DB on edit.
 *
 * JSON-list fields (objectives, audience, activities) are edited as
 * newline-separated text and joined back to JSON by the controller.
 */

// Convert stored JSON arrays to newline-separated text for the textareas.
$jsonToLines = static function ($value): string {
    if (empty($value)) {
        return '';
    }
    // The controller may hand us either a decoded array (on validation
    // failure from collectInput) or a JSON string (from the DB).
    if (is_string($value)) {
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return (string) $value;
        }
        $value = $decoded;
    }
    if (!is_array($value)) {
        return '';
    }
    return implode("\n", array_map('strval', $value));
};

$isNew = empty($programme['id']);
$formAction = $isNew
    ? '/admin/programmes'
    : '/admin/programmes/' . (int) $programme['id'] . '/edit';

// Field-level error helper.
$err = static function (string $field) use ($errors): bool {
    return isset($errors[$field]);
};
?>

<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error" role="alert">
        <p><strong>Please fix the following:</strong></p>
        <ul>
            <?php foreach ($errors as $message): ?>
                <li><?= e($message) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>

<form method="post" action="<?= e($formAction) ?>" class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <!-- ============================================================
         Identity
         ============================================================ -->
    <fieldset class="form__group">
        <legend>Identity</legend>

        <div class="form__row">
            <label for="title" class="form__label">
                Title <span class="form__required" aria-hidden="true">*</span>
            </label>
            <input type="text"
                   id="title"
                   name="title"
                   class="form__input"
                   required
                   maxlength="190"
                   value="<?= e($programme['title']) ?>"
                   aria-invalid="<?= $err('title') ? 'true' : 'false' ?>"
                   aria-describedby="<?= $err('title') ? 'title-error' : '' ?>">
            <?php if ($err('title')): ?>
                <p class="form__error" id="title-error"><?= e($errors['title']) ?></p>
            <?php endif; ?>
        </div>

        <div class="form__grid">
            <div class="form__row">
                <label for="slug" class="form__label">
                    Slug
                    <span class="form__hint">a–z, 0–9, hyphens. Auto-generated if left blank.</span>
                </label>
                <input type="text"
                       id="slug"
                       name="slug"
                       class="form__input"
                       maxlength="120"
                       value="<?= e($programme['slug']) ?>"
                       aria-invalid="<?= $err('slug') ? 'true' : 'false' ?>">
                <?php if ($err('slug')): ?>
                    <p class="form__error"><?= e($errors['slug']) ?></p>
                <?php endif; ?>
            </div>

            <div class="form__row">
                <label for="kind" class="form__label">Kind</label>
                <select id="kind" name="kind" class="form__select">
                    <?php foreach (['programme' => 'Programme', 'project' => 'Project'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $programme['kind'] === $value ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__row">
            <label for="category" class="form__label">
                Category
                <span class="form__hint">optional — e.g. youth, enterprise, agriculture, community</span>
            </label>
            <input type="text"
                   id="category"
                   name="category"
                   class="form__input"
                   maxlength="80"
                   value="<?= e($programme['category'] ?? '') ?>">
        </div>
    </fieldset>

    <!-- ============================================================
         Public content
         ============================================================ -->
    <fieldset class="form__group">
        <legend>Public content</legend>

        <div class="form__row">
            <label for="summary" class="form__label">
                Summary
                <span class="form__hint">shown on cards and listings, ~1–2 sentences</span>
            </label>
            <textarea id="summary"
                      name="summary"
                      class="form__textarea"
                      rows="2"
                      maxlength="500"><?= e($programme['summary'] ?? '') ?></textarea>
        </div>

        <div class="form__row">
            <label for="body" class="form__label">
                Full description
                <span class="form__hint">plain text, paragraph breaks preserved</span>
            </label>
            <textarea id="body"
                      name="body"
                      class="form__textarea"
                      rows="12"><?= e($programme['body'] ?? '') ?></textarea>
        </div>

        <div class="form__row">
            <label for="outcomes" class="form__label">
                Outcomes
                <span class="form__hint">verified results only. Leave blank if none yet.</span>
            </label>
            <textarea id="outcomes"
                      name="outcomes"
                      class="form__textarea"
                      rows="4"><?= e($programme['outcomes'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <!-- ============================================================
         Structured lists
         ============================================================ -->
    <fieldset class="form__group">
        <legend>Structured lists</legend>
        <p class="form__hint">
            One item per line. Each line becomes a list item on the public page.
        </p>

        <div class="form__row">
            <label for="objectives" class="form__label">Objectives</label>
            <textarea id="objectives"
                      name="objectives"
                      class="form__textarea"
                      rows="4"
                      placeholder="Improve employability of participants&#10;Strengthen enterprise capacity&#10;Build digital skills"><?= e($jsonToLines($programme['objectives'] ?? '')) ?></textarea>
        </div>

        <div class="form__row">
            <label for="audience" class="form__label">Target audience</label>
            <textarea id="audience"
                      name="audience"
                      class="form__textarea"
                      rows="3"
                      placeholder="Young people aged 18–30&#10;Emerging SMEs in Lilongwe&#10;Cooperatives and community groups"><?= e($jsonToLines($programme['audience'] ?? '')) ?></textarea>
        </div>

        <div class="form__row">
            <label for="activities" class="form__label">Activities</label>
            <textarea id="activities"
                      name="activities"
                      class="form__textarea"
                      rows="5"
                      placeholder="Workshops and training sessions&#10;One-to-one coaching&#10;Market linkage support&#10;Follow-up mentorship"><?= e($jsonToLines($programme['activities'] ?? '')) ?></textarea>
        </div>
    </fieldset>

    <!-- ============================================================
         Status and timeline
         ============================================================ -->
    <fieldset class="form__group">
        <legend>Status and timeline</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="status" class="form__label">
                    Programme status
                    <span class="form__hint">shown as a pill on the public page</span>
                </label>
                <select id="status" name="status" class="form__select">
                    <?php
                    $statuses = [
                        'proposed'       => 'Proposed',
                        'in_development' => 'In development',
                        'active'         => 'Active',
                        'completed'      => 'Completed',
                        'paused'         => 'Paused',
                        'archived'       => 'Archived',
                    ];
                    foreach ($statuses as $value => $label):
                    ?>
                        <option value="<?= e($value) ?>" <?= $programme['status'] === $value ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form__row">
                <label for="workflow_status" class="form__label">
                    Publication status
                    <span class="form__hint">controls whether the public site shows it at all</span>
                </label>
                <select id="workflow_status" name="workflow_status" class="form__select">
                    <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $value => $label): ?>
                        <option value="<?= e($value) ?>" <?= $programme['workflow_status'] === $value ? 'selected' : '' ?>>
                            <?= e($label) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__grid">
            <div class="form__row">
                <label for="start_date" class="form__label">Start date</label>
                <input type="date"
                       id="start_date"
                       name="start_date"
                       class="form__input"
                       value="<?= e($programme['start_date'] ?? '') ?>">
            </div>

            <div class="form__row">
                <label for="end_date" class="form__label">End date</label>
                <input type="date"
                       id="end_date"
                       name="end_date"
                       class="form__input"
                       value="<?= e($programme['end_date'] ?? '') ?>">
            </div>
        </div>

        <div class="form__row">
            <label for="timeline" class="form__label">
                Timeline description
                <span class="form__hint">free text — e.g. "Phase 1: Jan–Mar 2026"</span>
            </label>
            <input type="text"
                   id="timeline"
                   name="timeline"
                   class="form__input"
                   maxlength="190"
                   value="<?= e($programme['timeline'] ?? '') ?>">
        </div>

        <div class="form__row form__row--check">
            <input type="checkbox"
                   id="is_featured"
                   name="is_featured"
                   value="1"
                   <?= !empty($programme['is_featured']) ? 'checked' : '' ?>>
            <label for="is_featured">Feature on the homepage</label>
        </div>
    </fieldset>

    <!-- ============================================================
         SEO
         ============================================================ -->
    <fieldset class="form__group">
        <legend>SEO</legend>

        <div class="form__row">
            <label for="meta_title" class="form__label">
                Meta title
                <span class="form__hint">leave blank to use the programme title</span>
            </label>
            <input type="text"
                   id="meta_title"
                   name="meta_title"
                   class="form__input"
                   maxlength="190"
                   value="<?= e($programme['meta_title'] ?? '') ?>">
        </div>

        <div class="form__row">
            <label for="meta_description" class="form__label">
                Meta description
                <span class="form__hint">~150–160 characters is ideal</span>
            </label>
            <textarea id="meta_description"
                      name="meta_description"
                      class="form__textarea"
                      rows="2"
                      maxlength="300"><?= e($programme['meta_description'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <!-- ============================================================
         Actions
         ============================================================ -->
    <div class="form__actions">
        <a href="/admin/programmes" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">
            <?= $isNew ? 'Create programme' : 'Save changes' ?>
        </button>
    </div>
</form>

<!-- ============================================================
     Danger zone — only shown on edit
     ============================================================ -->
<?php if (!$isNew): ?>
<div class="admin__panel admin__panel--danger">
    <h2>Danger zone</h2>
    <p class="admin__panel-hint">
        Deleting is permanent. If you only want to hide the programme from the
        public site, set <strong>Publication status</strong> to <em>Archived</em>
        instead and save.
    </p>
    <form method="post"
          action="/admin/programmes/<?= (int) $programme['id'] ?>/delete"
          onsubmit="return confirm('Delete &quot;<?= e($programme['title']) ?>&quot; permanently? This cannot be undone.');">
        <?= $csrf->field() ?>
        <button type="submit" class="btn btn--sm btn--danger">Delete programme</button>
    </form>
</div>
<?php endif; ?>