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
      action="<?= !empty($article['id']) ? '/admin/news/' . (int) $article['id'] . '/edit' : '/admin/news' ?>"
      enctype="multipart/form-data" class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <fieldset class="form__group">
        <legend>Article</legend>

        <div class="form__row">
            <label for="title" class="form__label">Title <span class="form__required">*</span></label>
            <input type="text" id="title" name="title" class="form__input" required
                   value="<?= e($article['title']) ?>" maxlength="190">
        </div>

        <div class="form__grid">
            <div class="form__row">
                <label for="slug" class="form__label">Slug</label>
                <input type="text" id="slug" name="slug" class="form__input"
                       value="<?= e($article['slug']) ?>" maxlength="160">
            </div>
            <div class="form__row">
                <label for="category_id" class="form__label">Category</label>
                <select id="category_id" name="category_id" class="form__select">
                    <option value="0">— None —</option>
                    <?php foreach ($categories as $c): ?>
                        <option value="<?= (int) $c['id'] ?>" <?= (int) ($article['category_id'] ?? 0) === (int) $c['id'] ? 'selected' : '' ?>>
                            <?= e($c['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form__row">
            <label for="excerpt" class="form__label">Excerpt <span class="form__required">*</span> <span class="form__hint">one or two sentences, shown in listings</span></label>
            <textarea id="excerpt" name="excerpt" class="form__textarea" rows="2" maxlength="500" required><?= e($article['excerpt']) ?></textarea>
        </div>

        <div class="form__row">
            <label for="body" class="form__label">Body</label>
            <textarea id="body" name="body" class="form__textarea" rows="14"><?= e($article['body']) ?></textarea>
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>Publishing</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="workflow_status" class="form__label">Status</label>
                <select id="workflow_status" name="workflow_status" class="form__select">
                    <?php foreach (['draft' => 'Draft', 'published' => 'Published', 'archived' => 'Archived'] as $v => $lbl): ?>
                        <option value="<?= e($v) ?>" <?= $article['workflow_status'] === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
                    <?php endforeach; ?>
                </select>
                <p class="form__hint">Scheduled: set status to Published with a future date below.</p>
            </div>
            <div class="form__row">
                <label for="published_at" class="form__label">Publish date and time</label>
                <input type="datetime-local" id="published_at" name="published_at" class="form__input"
                       value="<?= e(date('Y-m-d\TH:i', strtotime($article['published_at'] ?? 'now'))) ?>">
            </div>
        </div>

        <div class="form__row">
            <label for="image" class="form__label">Featured image</label>
            <?php if (!empty($article['media_filename'])): ?>
                <div class="form__preview">
                    <img src="<?= e(asset('uploads/' . $article['media_filename'])) ?>" alt="">
                </div>
            <?php endif; ?>
            <input type="file" id="image" name="image" class="form__input" accept="image/jpeg,image/png,image/webp">
            <input type="text" name="image_alt" class="form__input" placeholder="Image alternative text" style="margin-top:6px"
                   value="<?= e($article['media_alt'] ?? '') ?>" maxlength="250">
        </div>
    </fieldset>

    <fieldset class="form__group">
        <legend>SEO</legend>
        <div class="form__row">
            <label for="meta_title" class="form__label">Meta title</label>
            <input type="text" id="meta_title" name="meta_title" class="form__input"
                   value="<?= e($article['meta_title'] ?? '') ?>" maxlength="190">
        </div>
        <div class="form__row">
            <label for="meta_description" class="form__label">Meta description</label>
            <textarea id="meta_description" name="meta_description" class="form__textarea" rows="2" maxlength="300"><?= e($article['meta_description'] ?? '') ?></textarea>
        </div>
    </fieldset>

    <div class="form__actions">
        <a href="/admin/news" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save</button>
    </div>
</form>

<?php if (!empty($article['id'])): ?>
<div class="admin__panel">
    <h2>Danger zone</h2>
    <form method="post" action="/admin/news/<?= (int) $article['id'] ?>/delete"
          onsubmit="return confirm('Delete this article permanently?');">
        <?= $csrf->field() ?>
        <button type="submit" class="btn btn--sm">Delete article</button>
    </form>
</div>
<?php endif; ?>