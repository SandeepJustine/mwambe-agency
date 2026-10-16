<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Partnership enquiries</h2>
    </header>

    <?php if (empty($enquiries)): ?>
        <p class="admin__empty">No partnership enquiries yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Received</th>
                    <th>Organisation</th>
                    <th>Contact</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enquiries as $e): ?>
                    <tr>
                        <td><a href="/admin/enquiries/partnership/<?= (int) $e['id'] ?>"><code><?= e($e['reference']) ?></code></a></td>
                        <td><time datetime="<?= e($e['created_at']) ?>"><?= e(date('d M Y H:i', strtotime($e['created_at']))) ?></time></td>
                        <td><?= e($e['organisation_name']) ?><br><small><?= e($e['organisation_type'] ?: '') ?></small></td>
                        <td><?= e($e['contact_person']) ?><br><small><?= e($e['email']) ?></small></td>
                        <td><span class="status status--<?= e($e['status']) ?>"><?= e(str_replace('_', ' ', $e['status'])) ?></span></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>