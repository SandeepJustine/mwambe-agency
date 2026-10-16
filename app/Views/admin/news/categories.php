<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <h2>Add category</h2>
    <form method="post" action="/admin/news/categories" class="form form--admin">
        <?= $csrf->field() ?>
        <div class="form__row">
            <label for="name" class="form__label">Category name</label>
            <input type="text" id="name" name="name" class="form__input" required maxlength="120" placeholder="e.g. Announcements, Programme updates">
        </div>
        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Add</button>
        </div>
    </form>
</div>

<div class="admin__panel">
    <h2>Existing categories</h2>
    <?php if (empty($categories)): ?>
        <p class="admin__empty">No categories yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr><th>Name</th><th>Slug</th><th>Articles</th><th></th></tr>
            </thead>
            <tbody>
                <?php foreach ($categories as $c): ?>
                    <tr>
                        <td><?= e($c['name']) ?></td>
                        <td><code><?= e($c['slug']) ?></code></td>
                        <td><?= (int) $c['article_count'] ?></td>
                        <td>
                            <a href="/admin/news/categories/<?= (int) $c['id'] ?>/edit" class="btn btn--sm">Edit</a>
                            <form method="post" action="/admin/news/categories/<?= (int) $c['id'] ?>/delete"
                                  onsubmit="return confirm('Delete this category? Articles using it will become uncategorised.');">
                                <?= $csrf->field() ?>
                                <button type="submit" class="btn btn--sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

<p><a href="/admin/news">← Back to articles</a></p>