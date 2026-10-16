<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Impact indicators</h2>
        <a href="/admin/impact/new" class="btn btn--sm btn--primary">Add indicator</a>
    </header>

    <div class="callout callout--info" style="margin-bottom:var(--sp-5)">
        <p style="margin:0">
            <strong>Publishing rule:</strong> Only indicators marked
            <em>Verified</em> and <em>public</em> appear on the public website.
            Pending figures never show publicly.
        </p>
    </div>

    <?php if (empty($indicators)): ?>
        <p class="admin__empty">No indicators yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th>Order</th>
                    <th>Label</th>
                    <th>Value</th>
                    <th>Verification</th>
                    <th>Public</th>
                    <th>Reviewed by</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($indicators as $ind): ?>
                    <tr>
                        <td><?= (int) $ind['position'] ?></td>
                        <td><?= e($ind['label']) ?><br><small><code><?= e($ind['key_name']) ?></code></small></td>
                        <td><?= e($ind['value']) ?><?= e($ind['unit'] ?? '') ?></td>
                        <td>
                            <?php if ($ind['verification_status'] === 'verified'): ?>
                                <span class="status status--active">Verified</span>
                            <?php elseif ($ind['verification_status'] === 'rejected'): ?>
                                <span class="status status--paused">Rejected</span>
                            <?php else: ?>
                                <span class="status status--in_development">Pending</span>
                            <?php endif; ?>
                        </td>
                        <td><?= (int) $ind['is_public'] === 1 ? 'Yes' : 'No' ?></td>
                        <td>
                            <?= e($ind['reviewer_name'] ?? '—') ?>
                            <?php if (!empty($ind['approved_at'])): ?>
                                <br><small><?= e(date('d M Y', strtotime($ind['approved_at']))) ?></small>
                            <?php endif; ?>
                        </td>
                        <td><a href="/admin/impact/<?= (int) $ind['id'] ?>/edit" class="btn btn--sm">Edit</a></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>