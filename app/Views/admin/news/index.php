<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>News &amp; Insights</h2>
        <div class="button-row">
            <a href="/admin/news/categories" class="btn btn--sm btn--ghost-light">Manage categories</a>
            <a href="/admin/news/new" class="btn btn--sm btn--primary">New article</a>
        </div>
    </header>

    <?php if (empty($articles)): ?>
        <p class="admin__empty">No articles yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>Status</th>
                    <th>Published</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($articles as $a): ?>
                    <tr>
                        <td><?= e($a['title']) ?><br><small><code><?= e($a['slug']) ?></code></small></td>
                        <td><?= e($a['category_name'] ?? '—') ?></td>
                        <td><span class="status status--<?= e($a['workflow_status']) ?>"><?= e($a['workflow_status']) ?></span></td>
                        <td>
                            <?php if ($a['published_at']): ?>
                                <time datetime="<?= e($a['published_at']) ?>"><?= e(date('d M Y H:i', strtotime($a['published_at']))) ?></time>
                            <?php else: ?>
                                —
                            <?php endif; ?>
                        </td>
                        <td><?= e(date('d M Y', strtotime($a['updated_at']))) ?></td>
                        <td>
                            <a href="/admin/news/<?= (int) $a['id'] ?>/edit" class="btn btn--sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>