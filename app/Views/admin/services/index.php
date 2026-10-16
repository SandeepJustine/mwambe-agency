<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Services</h2>
        <a href="/admin/services/new" class="btn btn--sm btn--primary">Add service</a>
    </header>

    <?php if (empty($services)): ?>
        <p class="admin__empty">No services yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Name</th>
                    <th>Category</th>
                    <th>Featured</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($services as $s): ?>
                    <tr>
                        <td><?= (int) $s['position'] ?></td>
                        <td><?= e($s['name']) ?><br><small><code><?= e($s['slug']) ?></code></small></td>
                        <td><?= e($s['category'] ?? '—') ?></td>
                        <td><?= (int) $s['is_featured'] === 1 ? 'Yes' : '—' ?></td>
                        <td><span class="status status--<?= e($s['workflow_status']) ?>"><?= e($s['workflow_status']) ?></span></td>
                        <td><a href="/admin/services/<?= (int) $s['id'] ?>/edit" class="btn btn--sm">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>