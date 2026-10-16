<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <h2>Upload image</h2>
    <form method="post" action="/admin/media" enctype="multipart/form-data" class="form form--admin">
        <?= $csrf->field() ?>
        <div class="form__row">
            <label for="image" class="form__label">Image file
                <span class="form__hint">JPEG, PNG, WebP — max 5 MB</span>
            </label>
            <input type="file" id="image" name="image" class="form__input" required
                   accept="image/jpeg,image/png,image/webp">
        </div>
        <div class="form__grid">
            <div class="form__row">
                <label for="alt_text" class="form__label">Alternative text</label>
                <input type="text" id="alt_text" name="alt_text" class="form__input" maxlength="250">
            </div>
            <div class="form__row">
                <label for="caption" class="form__label">Caption</label>
                <input type="text" id="caption" name="caption" class="form__input" maxlength="500">
            </div>
        </div>
        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Upload</button>
        </div>
    </form>
</div>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Media library</h2>
        <form method="get" class="admin__filter">
            <label for="q" class="visually-hidden">Search</label>
            <input type="search" id="q" name="q" value="<?= e($query) ?>" placeholder="Search by name, alt text or caption">
            <button type="submit" class="btn btn--sm">Search</button>
        </form>
    </header>

    <p class="admin__panel-hint"><?= (int) $total ?> file<?= $total === 1 ? '' : 's' ?></p>

    <?php if (empty($media)): ?>
        <p class="admin__empty">No media yet.</p>
    <?php else: ?>
        <div class="media-grid">
            <?php foreach ($media as $m): ?>
                <article class="media-item<?= $m['reference_count'] > 0 ? ' media-item--used' : '' ?>">
                    <div class="media-item__thumb">
                        <img src="<?= e(asset('uploads/' . $m['filename'])) ?>"
                             alt="<?= e($m['alt_text'] ?? '') ?>" loading="lazy">
                        <?php if ($m['reference_count'] > 0): ?>
                            <span class="media-item__badge" title="In use in <?= (int) $m['reference_count'] ?> place(s)">
                                In use ×<?= (int) $m['reference_count'] ?>
                            </span>
                        <?php endif; ?>
                    </div>

                    <div class="media-item__body">
                        <p class="media-item__name" title="<?= e($m['original_name']) ?>">
                            <?= e(mb_strimwidth($m['original_name'], 0, 40, '…')) ?>
                        </p>
                        <p class="media-item__meta">
                            <?= number_format($m['size_bytes'] / 1024, 0) ?> KB
                            <?php if ($m['width'] && $m['height']): ?>
                                · <?= (int) $m['width'] ?>×<?= (int) $m['height'] ?>
                            <?php endif; ?>
                        </p>
                        <p class="media-item__meta">
                            <?= e(date('d M Y', strtotime($m['created_at']))) ?>
                            <?php if ($m['uploaded_by_name']): ?>
                                · <?= e($m['uploaded_by_name']) ?>
                            <?php endif; ?>
                        </p>

                        <details class="media-item__edit">
                            <summary>Edit metadata</summary>
                            <form method="post" action="/admin/media/<?= (int) $m['id'] ?>/edit" class="form">
                                <?= $csrf->field() ?>
                                <label for="alt_<?= (int) $m['id'] ?>" class="form__label">Alt text</label>
                                <input type="text" id="alt_<?= (int) $m['id'] ?>" name="alt_text"
                                       class="form__input" value="<?= e($m['alt_text'] ?? '') ?>" maxlength="250">
                                <label for="cap_<?= (int) $m['id'] ?>" class="form__label">Caption</label>
                                <input type="text" id="cap_<?= (int) $m['id'] ?>" name="caption"
                                       class="form__input" value="<?= e($m['caption'] ?? '') ?>" maxlength="500">
                                <button type="submit" class="btn btn--sm btn--primary">Save</button>
                            </form>
                        </details>

                        <?php if ($m['reference_count'] === 0): ?>
                            <form method="post" action="/admin/media/<?= (int) $m['id'] ?>/delete"
                                  onsubmit="return confirm('Delete this file from the server?');"
                                  class="media-item__delete">
                                <?= $csrf->field() ?>
                                <button type="submit" class="btn btn--sm">Delete</button>
                            </form>
                        <?php endif; ?>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>

        <?php $pages = (int) ceil($total / $perPage); if ($pages > 1): ?>
            <nav class="pagination" aria-label="Pagination">
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="is-current"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?><?= $query ? '&q=' . urlencode($query) : '' ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>