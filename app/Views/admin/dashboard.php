<?php
/**
 * Admin dashboard.
 *
 * Context passed in by DashboardController::index():
 *   $stats          array<string,int>   counts for the stat cards
 *   $needsAttention array<int,array{label:string,url:string}>
 *   $recentAudit    array<int,array>    last 10 audit rows
 *   $auth           Auth
 *   $csrf           Csrf
 */

// Human-readable labels for audit action codes.
$actionLabels = [
    'user.login'              => 'Signed in',
    'user.logout'             => 'Signed out',
    'user.create'             => 'Created user',
    'user.update'             => 'Updated user',
    'user.password_change'    => 'Changed own password',
    'user.force_logout'       => 'Revoked user sessions',
    'user.reset_sent'         => 'Sent password reset',
    'homepage.slide.update'   => 'Updated hero slide',
    'programme.create'        => 'Created programme',
    'programme.update'        => 'Updated programme',
    'programme.delete'        => 'Deleted programme',
    'sector.create'           => 'Created sector',
    'sector.update'           => 'Updated sector',
    'sector.delete'           => 'Deleted sector',
    'service.create'          => 'Created service',
    'service.update'          => 'Updated service',
    'service.delete'          => 'Deleted service',
    'news.create'             => 'Created article',
    'news.update'             => 'Updated article',
    'news.delete'             => 'Deleted article',
    'news.category.create'    => 'Created news category',
    'news.category.delete'    => 'Deleted news category',
    'event.create'            => 'Created event',
    'event.update'            => 'Updated event',
    'event.delete'            => 'Deleted event',
    'impact.create'           => 'Created impact indicator',
    'impact.update'           => 'Updated impact indicator',
    'document.upload'         => 'Uploaded document',
    'document.delete'         => 'Deleted document',
    'media.upload'            => 'Uploaded media',
    'media.update'            => 'Updated media',
    'media.delete'            => 'Deleted media',
    'enquiry.update'          => 'Updated enquiry',
];
?>

<?php if (!empty($needsAttention)): ?>
    <section class="admin__attention" aria-label="Needs attention">
        <h2 class="admin__attention-title">Needs attention</h2>
        <ul class="admin__attention-list">
            <?php foreach ($needsAttention as $item): ?>
                <li>
                    <a href="<?= e($item['url']) ?>">
                        <?= e($item['label']) ?>
                        <span aria-hidden="true">→</span>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>
<?php endif; ?>

<section class="admin__grid">
    <article class="stat">
        <p class="stat__label">Active hero slides</p>
        <p class="stat__value">
            <?= (int) $stats['slides'] ?><span class="stat__of"> / 3</span>
        </p>
        <p class="stat__hint">Fixed at exactly 3 by design.</p>
    </article>

    <article class="stat">
        <p class="stat__label">Media images</p>
        <p class="stat__value"><?= (int) $stats['media'] ?></p>
        <p class="stat__hint">
            Images in the <a href="/admin/media">media library</a>.
        </p>
    </article>

    <article class="stat">
        <p class="stat__label">Documents</p>
        <p class="stat__value"><?= (int) $stats['documents'] ?></p>
        <p class="stat__hint">
            Files in <a href="/admin/documents">downloads</a>.
        </p>
    </article>

    <article class="stat">
        <p class="stat__label">Active users</p>
        <p class="stat__value"><?= (int) $stats['users'] ?></p>
        <p class="stat__hint">Staff with login access.</p>
    </article>

    <article class="stat">
        <p class="stat__label">Actions logged today</p>
        <p class="stat__value"><?= (int) $stats['audit_today'] ?></p>
        <p class="stat__hint">Administrative activity.</p>
    </article>
</section>

<section class="admin__panel">
    <header class="admin__panel-head">
        <h2>Recent activity</h2>
        <?php if ($auth->hasRole('super_admin', 'admin')): ?>
            <a href="/admin/system/audit" class="admin__panel-link">View full log →</a>
        <?php endif; ?>
    </header>

    <?php if (empty($recentAudit)): ?>
        <p class="admin__empty">No activity recorded yet. Actions you take will appear here.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th scope="col">When</th>
                    <th scope="col">User</th>
                    <th scope="col">Action</th>
                    <th scope="col">Details</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($recentAudit as $row): ?>
                    <?php
                    $label = $actionLabels[$row['action']] ?? $row['action'];
                    $hasEntity = !empty($row['entity_type']) && !empty($row['entity_id']);
                    ?>
                    <tr>
                        <td>
                            <time datetime="<?= e($row['created_at']) ?>">
                                <?= e(date('d M Y H:i', strtotime($row['created_at']))) ?>
                            </time>
                        </td>
                        <td><?= e($row['user_name'] ?? 'System') ?></td>
                        <td><?= e($label) ?></td>
                        <td>
                            <?php if ($hasEntity): ?>
                                <code><?= e($row['entity_type']) ?>#<?= (int) $row['entity_id'] ?></code>
                            <?php endif; ?>
                            <?php if (!empty($row['summary'])): ?>
                                <?= $hasEntity ? ' ' : '' ?><?= e($row['summary']) ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</section>