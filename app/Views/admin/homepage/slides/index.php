<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Homepage hero slider</h2>
        <p class="admin__panel-hint">The homepage always shows exactly 3 slides. Edit any of them below.</p>
    </header>

    <ul class="slidelist">
        <?php foreach ($slides as $s): ?>
            <li class="slidelist__item">
                <div class="slidelist__pos"><?= (int) $s['position'] ?></div>

                <div class="slidelist__thumb">
                    <?php if (!empty($s['media_filename'])): ?>
                        <img src="<?= e(asset('uploads/' . $s['media_filename'])) ?>" alt="<?= e($s['media_alt'] ?? '') ?>">
                    <?php else: ?>
                        <span class="slidelist__thumb-geo" aria-hidden="true"></span>
                    <?php endif; ?>
                </div>

                <div class="slidelist__body">
                    <p class="slidelist__eyebrow"><?= e($s['eyebrow'] ?? '') ?></p>
                    <p class="slidelist__headline"><?= e($s['headline']) ?></p>
                    <p class="slidelist__meta">
                        <code><?= e($s['layout']) ?></code>
                        · <?= e($s['workflow_status']) ?>
                        · <?= (int) $s['is_active'] === 1 ? 'active' : 'inactive' ?>
                    </p>
                </div>

                <div class="slidelist__actions">
                    <a href="/admin/homepage/slides/<?= (int) $s['id'] ?>/edit" class="btn btn--sm btn--primary">Edit</a>
                </div>
            </li>
        <?php endforeach; ?>
    </ul>
</div>