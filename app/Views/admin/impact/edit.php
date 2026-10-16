<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error" role="alert">
        <p><strong>Please fix:</strong></p>
        <ul><?php foreach ($errors as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<?php if (!empty($indicator['id']) && ($indicator['verification_status'] ?? 'pending') === 'pending'): ?>
    <div class="alert alert--error" role="alert">
        <strong>⚠ This indicator is pending verification and will NOT appear on the public website.</strong>
        Set status to "Verified" and provide an evidence reference to publish it.
    </div>
<?php endif; ?>

<form method="post"
      action="<?= !empty($indicator['id']) ? '/admin/impact/' . (int) $indicator['id'] . '/edit' : '/admin/impact' ?>"
      class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <fieldset class="form__group">
        <legend>Indicator</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="label" class="form__label">Label <span class="form__required">*</span></label>
                <input type="text" id="label" name="label" class="form__input" required
                       value="<?= e($indicator['label']) ?>" maxlength="120">
            </div>
            <div class="form__row">
                <label for="key_name" class="form__label">Key name <span class="form__hint">system identifier, a-z0-9_</span></label>
                <input type="text" id="key_name" name="key_name" class="form__input"
                       value="<?= e($indicator['key_name'] ?? '') ?>" maxlength="80"
                       <?= !empty($indicator['id']) ? 'readonly' : '' ?>>
            </div>
            <div class="form__row">
                <label for="value" class="form__label">Value <span class="form__required">*</span></label>
                <input type="text" id="value" name="value" class="form__input" required
                       value="<?= e($indicator['value']) ?>" maxlength="40">
            </div>
            <div class="form__row">
                <label for="unit" class="form__label">Unit <span class="form__hint">e.g. "+", "%"</span></label>
                <input type="text" id="unit" name="unit" class="form__input"
                       value="<?= e($indicator['unit'] ?? '') ?>" maxlength="40">
            </div>
            <div class="form__row">
                <label for="position" class="form__label">Display order</label>
                <input type="number" id="position" name="position" class="form__input"
                       value="<?= (int) ($indicator['position'] ?? 0) ?>" min="0" max="999">
            </div>
        </div>

        <div class="form__row">
            <label for="description" class="form__label">Description</label>
            <textarea id="description" name="description" class="form__textarea" rows="3" maxlength="500"><?= e($indicator['description'] ?? '') ?></textarea>
        </div>

        <div class="form__grid">
            <div class="form__row">
                <label for="reporting_period" class="form__label">Reporting period</label>
                <input type="text" id="reporting_period" name="reporting_period" class="form__input"
                       value="<?= e($indicator['reporting_period'] ?? '') ?>" maxlength="120"
                       placeholder="e.g. To date, Jan–Dec 2025">
            </div>
            <div class="form__row">
                <label for="evidence_reference" class="form__label">
                    Evidence reference
                    <span class="form__hint">required before verification</span>
                </label>
                <input type="text" id="evidence_reference" name="evidence_reference" class="form__input"
                       value="<?= e($indicator['evidence_reference'] ?? '') ?>" maxlength="500"
                       placeholder="e.g. Programme register 2025, MEAL report Q4">
            </div>
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>Verification &amp; publishing</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="verification_status" class="form__label">Verification status</label>
                <?php if ($auth->hasRole('super_admin', 'admin')): ?>
                    <select id="verification_status" name="verification_status" class="form__select">
                        <?php foreach (['pending' => 'Pending', 'verified' => 'Verified', 'rejected' => 'Rejected'] as $v => $lbl): ?>
                            <option value="<?= e($v) ?>" <?= ($indicator['verification_status'] ?? 'pending') === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
                        <?php endforeach; ?>
                    </select>
                <?php else: ?>
                    <input type="text" class="form__input" value="<?= e($indicator['verification_status'] ?? 'pending') ?>" disabled>
                    <input type="hidden" name="verification_status" value="<?= e($indicator['verification_status'] ?? 'pending') ?>">
                    <p class="form__hint">Only administrators can change verification status.</p>
                <?php endif; ?>
            </div>

            <div class="form__row form__row--check">
                <input type="checkbox" id="is_public" name="is_public" value="1"
                       <?= !empty($indicator['is_public']) ? 'checked' : '' ?>>
                <label for="is_public">Display on public website (only when verified)</label>
            </div>
        </div>
    </fieldset>

    <div class="form__actions">
        <a href="/admin/impact" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save</button>
    </div>
</form>