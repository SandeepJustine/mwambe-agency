<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Programmes &amp; Projects</h2>
        <a href="/admin/programmes/new" class="btn btn--sm btn--primary">New programme</a>
    </header>

    <?php if (empty($programmes)): ?>
        <p class="admin__empty">
            No programmes yet. <a href="/admin/programmes/new">Create the first one</a>.
        </p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th scope="col">Title</th>
                    <th scope="col">Kind</th>
                    <th scope="col">Category</th>
                    <th scope="col">Status</th>
                    <th scope="col">Featured</th>
                    <th scope="col">Updated</th>
                    <th scope="col"><span class="visually-hidden">Actions</span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($programmes as $p): ?>
                    <tr>
                        <td>
                            <a href="/admin/programmes/<?= (int) $p['id'] ?>/edit">
                                <?= e($p['title']) ?>
                            </a>
                            <br>
                            <small><code><?= e($p['slug']) ?></code></small>
                        </td>
                        <td><?= e($p['kind'] === 'project' ? 'Project' : 'Programme') ?></td>
                        <td><?= e($p['category'] ?: '—') ?></td>
                        <td>
                            <span class="status status--<?= e($p['status']) ?>">
                                <?= e(str_replace('_', ' ', $p['status'])) ?>
                            </span>
                            <br>
                            <small class="admin__table-workflow">
                                workflow: <?= e($p['workflow_status']) ?>
                            </small>
                        </td>
                        <td><?= (int) $p['is_featured'] === 1 ? 'Yes' : '—' ?></td>
                        <td>
                            <time datetime="<?= e($p['updated_at']) ?>">
                                <?= e(date('d M Y', strtotime($p['updated_at']))) ?>
                            </time>
                        </td>
                        <td>
                            <a href="/admin/programmes/<?= (int) $p['id'] ?>/edit" class="btn btn--sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>