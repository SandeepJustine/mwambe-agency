<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Users &amp; Roles</h2>
        <?php if ($auth->hasRole('super_admin')): ?>
            <a href="/admin/users/new" class="btn btn--sm btn--primary">Add user</a>
        <?php endif; ?>
    </header>

    <table class="admin__table">
        <thead>
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Roles</th>
                <th>Status</th>
                <th>Last login</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td><?= e($u['name']) ?></td>
                    <td><?= e($u['email']) ?></td>
                    <td>
                        <?php if (!empty($u['role_names'])): ?>
                            <?= e($u['role_names']) ?>
                        <?php else: ?>
                            <em style="color:#999">No roles</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ((int) $u['is_active'] === 1): ?>
                            <span class="status status--active">Active</span>
                        <?php else: ?>
                            <span class="status status--paused">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($u['last_login_at']): ?>
                            <time datetime="<?= e($u['last_login_at']) ?>"><?= e(date('d M Y H:i', strtotime($u['last_login_at']))) ?></time>
                        <?php else: ?>
                            <em style="color:#999">Never</em>
                        <?php endif; ?>
                    </td>
                    <td>
                        <?php if ($auth->hasRole('super_admin')): ?>
                            <a href="/admin/users/<?= (int) $u['id'] ?>/edit" class="btn btn--sm">Edit</a>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>