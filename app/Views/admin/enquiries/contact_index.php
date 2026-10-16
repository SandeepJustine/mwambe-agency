<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Contact enquiries</h2>
        <form method="get" class="admin__filter">
            <label for="status" class="visually-hidden">Filter by status</label>
            <select id="status" name="status" onchange="this.form.submit()">
                <?php foreach (['' => 'All statuses', 'new' => 'New', 'in_review' => 'In review', 'assigned' => 'Assigned', 'responded' => 'Responded', 'resolved' => 'Resolved', 'archived' => 'Archived'] as $v => $lbl): ?>
                    <option value="<?= e($v) ?>" <?= $statusFilter === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
                <?php endforeach; ?>
            </select>
        </form>
    </header>

    <?php if (empty($enquiries)): ?>
        <p class="admin__empty">No contact enquiries yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th>Reference</th>
                    <th>Received</th>
                    <th>From</th>
                    <th>Subject</th>
                    <th>Status</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($enquiries as $e): ?>
                    <tr>
                        <td><a href="/admin/enquiries/contact/<?= (int) $e['id'] ?>"><code><?= e($e['reference']) ?></code></a></td>
                        <td><time datetime="<?= e($e['created_at']) ?>"><?= e(date('d M Y H:i', strtotime($e['created_at']))) ?></time></td>
                        <td><?= e($e['name']) ?><br><small><?= e($e['email']) ?></small></td>
                        <td><?= e($e['subject'] ?: '—') ?></td>
                        <td><span class="status status--<?= e($e['status']) ?>"><?= e(str_replace('_', ' ', $e['status'])) ?></span></td>
                        <td>
                            <?php if ($e['email_sent_at']): ?><span title="Sent">✓</span>
                            <?php elseif ($e['email_error']): ?><span title="Failed: <?= e($e['email_error']) ?>" style="color:#C0392B">✗</span>
                            <?php else: ?><span style="color:#888">…</span><?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>