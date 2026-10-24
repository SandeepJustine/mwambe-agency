<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Sectors page introduction</h2>
    </header>
    <p>Manage the section heading, copy and button shown above the published sector cards.</p>
    <?php if ($pageSection === null): ?>
        <p class="admin__empty">The Sectors page has not been seeded yet.</p>
    <?php else: ?>
        <form method="post" action="/admin/sectors/page-section" class="form form--admin">
            <?= $csrf->field() ?>
            <div class="form__grid">
                <div class="form__row">
                    <label for="section_eyebrow" class="form__label">Eyebrow</label>
                    <input type="text" id="section_eyebrow" name="section_eyebrow" class="form__input" maxlength="120"
                           value="<?= e($pageSection['eyebrow'] ?? '') ?>">
                </div>
                <div class="form__row">
                    <label for="section_heading" class="form__label">Heading</label>
                    <input type="text" id="section_heading" name="section_heading" class="form__input" maxlength="190"
                           value="<?= e($pageSection['heading'] ?? '') ?>">
                </div>
            </div>
            <div class="form__row">
                <label for="section_body" class="form__label">Introduction</label>
                <textarea id="section_body" name="section_body" class="form__textarea" rows="4"><?= e($pageSection['body'] ?? '') ?></textarea>
            </div>
            <div class="form__grid">
                <div class="form__row">
                    <label for="cta_label" class="form__label">Button label</label>
                    <input type="text" id="cta_label" name="cta_label" class="form__input" maxlength="80"
                           value="<?= e($pageSection['cta_label'] ?? '') ?>">
                </div>
                <div class="form__row">
                    <label for="cta_url" class="form__label">Button destination</label>
                    <input type="text" id="cta_url" name="cta_url" class="form__input" maxlength="500"
                           value="<?= e($pageSection['cta_url'] ?? '') ?>" placeholder="/contact">
                </div>
            </div>
            <div class="form__row form__row--check">
                <input type="checkbox" id="section_visible" name="section_visible" value="1"
                       <?= !empty($pageSection['is_visible']) ? 'checked' : '' ?>>
                <label for="section_visible">Show this introduction above the sector cards</label>
            </div>
            <div class="form__actions">
                <button type="submit" class="btn btn--primary">Save page section</button>
            </div>
        </form>
    <?php endif; ?>
</div>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Sector cards</h2>
        <a href="/admin/sectors/new" class="btn btn--sm btn--primary">Add sector</a>
    </header>

    <?php if (empty($sectors)): ?>
        <p class="admin__empty">No sectors yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Name</th>
                    <th>Slug</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sectors as $s): ?>
                    <tr>
                        <td><?= (int) $s['position'] ?></td>
                        <td><?= e($s['name']) ?></td>
                        <td><code><?= e($s['slug']) ?></code></td>
                        <td><?= (int) $s['is_featured'] === 1 ? 'Yes' : '—' ?></td>
                        <td>
                            <span class="status status--<?= e($s['workflow_status']) ?>">
                                <?= e($s['workflow_status']) ?>
                            </span>
                        </td>
                        <td>
                            <a href="/admin/sectors/<?= (int) $s['id'] ?>/edit" class="btn btn--sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>