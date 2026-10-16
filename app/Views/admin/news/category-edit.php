<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($errors)): ?>
    <div class="alert alert--error" role="alert">
        <p><strong>Please fix:</strong></p>
        <ul><?php foreach ($errors as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post" action="/admin/news/categories/<?= (int) $category['id'] ?>/edit" class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <div class="form__group">
        <legend>Category</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="name" class="form__label">Name <span class="form__required">*</span></label>
                <input type="text" id="name" name="name" class="form__input" required
                       value="<?= e($category['name']) ?>" maxlength="120"
                       aria-invalid="<?= isset($errors['name']) ? 'true' : 'false' ?>">
                <?php if (isset($errors['name'])): ?><p class="form__error"><?= e($errors['name']) ?></p><?php endif; ?>
            </div>
            <div class="form__row">
                <label for="slug" class="form__label">Slug <span class="form__hint">editable; auto-generated from name</span></label>
                <input type="text" id="slug" name="slug" class="form__input"
                       value="<?= e($category['slug']) ?>" maxlength="120"
                       aria-invalid="<?= isset($errors['slug']) ? 'true' : 'false' ?>">
                <?php if (isset($errors['slug'])): ?><p class="form__error"><?= e($errors['slug']) ?></p><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="form__actions">
        <a href="/admin/news/categories" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save</button>
    </div>
</form>

<div class="admin__panel">
    <h2>Danger zone</h2>
    <form method="post" action="/admin/news/categories/<?= (int) $category['id'] ?>/delete"
          onsubmit="return confirm('Delete this category permanently? Articles using it will become uncategorised.');">
        <?= $csrf->field() ?>
        <button type="submit" class="btn btn--sm">Delete category</button>
    </form>
</div>

<p><a href="/admin/news/categories">← Back to categories</a></p>