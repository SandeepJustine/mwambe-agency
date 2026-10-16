<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Events &amp; Activities</h2>
        <div class="button-row">
            <a href="/admin/events/new" class="btn btn--sm btn--primary">New event</a>
        </div>
    </header>

    <?php if (empty($events)): ?>
        <p class="admin__empty">No events yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Starts</th>
                    <th>Location</th>
                    <th>Status</th>
                    <th>Updated</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($events as $ev): ?>
                    <tr>
                        <td><?= e($ev['title']) ?><br><small><code><?= e($ev['slug']) ?></code></small></td>
                        <td><?= e(date('d M Y H:i', strtotime($ev['starts_at']))) ?></td>
                        <td><?= e($ev['location'] ?? '—') ?></td>
                        <td><span class="status status--<?= e($ev['workflow_status']) ?>"><?= e($ev['workflow_status']) ?></span></td>
                        <td><?= e(date('d M Y', strtotime($ev['updated_at']))) ?></td>
                        <td>
                        <a href="/admin/events/<?= (int) $ev['id'] ?>/edit" class="btn btn--sm">Edit</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>