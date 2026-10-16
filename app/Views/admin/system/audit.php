<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Audit log</h2>
        <p class="admin__panel-hint"><?= (int) $total ?> total entries.</p>
    </header>

    <?php if (empty($rows)): ?>
        <p class="admin__empty">No activity recorded yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th scope="col">When</th>
                    <th scope="col">User</th>
                    <th scope="col">Action</th>
                    <th scope="col">Entity</th>
                    <th scope="col">Summary</th>
                    <th scope="col">IP</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $r): ?>
                    <tr>
                        <td><time datetime="<?= e($r['created_at']) ?>"><?= e(date('d M Y H:i', strtotime($r['created_at']))) ?></time></td>
                        <td><?= e($r['user_name'] ?? '—') ?><br><small><?= e($r['user_email'] ?? '') ?></small></td>
                        <td><code><?= e($r['action']) ?></code></td>
                        <td>
                            <?php if ($r['entity_type']): ?>
                                <?= e($r['entity_type']) ?>#<?= (int) $r['entity_id'] ?>
                            <?php endif; ?>
                        </td>
                        <td><?= e($r['summary'] ?? '') ?></td>
                        <td><small><?= e($r['ip_address'] ?? '') ?></small></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <?php if ($total > $per): ?>
            <nav class="admin__pagination" aria-label="Pagination">
                <?php $pages = (int) ceil($total / $per); ?>
                <?php for ($i = 1; $i <= $pages; $i++): ?>
                    <?php if ($i === $page): ?>
                        <span class="is-current" aria-current="page"><?= $i ?></span>
                    <?php else: ?>
                        <a href="?page=<?= $i ?>"><?= $i ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
            </nav>
        <?php endif; ?>
    <?php endif; ?>
</div>