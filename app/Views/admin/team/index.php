<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Team members</h2>
        <a href="/admin/team/new" class="btn btn--sm btn--primary">Add team member</a>
    </header>

    <?php if (empty($members)): ?>
        <p class="admin__empty">No team members yet. Add people to display them on the About page.</p>
    <?php else: ?>
        <div class="admin__table-wrap">
            <table class="admin__table">
                <thead>
                    <tr>
                        <th scope="col">Photo</th>
                        <th scope="col">Name</th>
                        <th scope="col">Position</th>
                        <th scope="col">Display order</th>
                        <th scope="col">Status</th>
                        <th scope="col">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member): ?>
                        <tr>
                            <td>
                                <?php if (!empty($member['media_filename'])): ?>
                                    <img src="<?= e(asset('uploads/' . $member['media_filename'])) ?>" alt="" width="48" height="48" style="object-fit:cover;border-radius:50%">
                                <?php else: ?>
                                    —
                                <?php endif; ?>
                            </td>
                            <td><?= e($member['name']) ?></td>
                            <td><?= e($member['position_title']) ?></td>
                            <td><?= (int) $member['position'] ?></td>
                            <td><?= (int) $member['is_active'] === 1 ? 'Active' : 'Hidden' ?></td>
                            <td><a href="/admin/team/<?= (int) $member['id'] ?>/edit" class="btn btn--sm">Edit</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
