<?php
/** @var \Mwambe\Core\Application $app */
/** @var \Mwambe\Core\Auth $auth */
/** @var \Mwambe\Core\Csrf $csrf */
$user = $auth->user();
$title = $title ?? 'Admin';
$activeNav = $activeNav ?? '';
$breadcrumbs = $breadcrumbs ?? [];
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title) ?> — MWAMBE Admin</title>
    <link rel="icon" type="image/png" href="<?= e(asset('assets/img/mwambe-logo.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">
<div class="admin__shell" data-admin-shell>

    <!-- Sidebar -->
    <aside class="admin__sidebar" id="admin-sidebar" aria-label="Admin navigation">
        <div class="admin__sidebar-brand">
            <img src="<?= e(asset('assets/img/mwambe-logo.png')) ?>" alt="MWAMBE Agency" width="120" height="120">
            <span class="admin__sidebar-brand-text">CMS</span>
        </div>

        <nav class="admin__nav">
            <p class="admin__nav-group">Overview</p>
            <a href="/admin" class="admin__nav-link<?= $activeNav === 'dashboard' ? ' is-active' : '' ?>">Dashboard</a>

            <p class="admin__nav-group">Website Content</p>
            <a href="/admin/homepage/slides" class="admin__nav-link<?= $activeNav === 'slides' ? ' is-active' : '' ?>">Hero Slider</a>
            <a href="/admin/services"        class="admin__nav-link<?= $activeNav === 'services' ? ' is-active' : '' ?>">Services</a>
            <a href="/admin/sectors"         class="admin__nav-link<?= $activeNav === 'sectors' ? ' is-active' : '' ?>">Sectors</a>
            <a href="/admin/media"           class="admin__nav-link<?= $activeNav === 'media' ? ' is-active' : '' ?>">Media Library</a>
            <a href="/admin/documents"       class="admin__nav-link<?= $activeNav === 'documents' ? ' is-active' : '' ?>">Documents</a>

            <p class="admin__nav-group">Programmes &amp; Activities</p>
            <a href="/admin/programmes" class="admin__nav-link<?= $activeNav === 'programmes' ? ' is-active' : '' ?>">Programmes &amp; Projects</a>
            <a href="/admin/news"       class="admin__nav-link<?= $activeNav === 'news' ? ' is-active' : '' ?>">News &amp; Insights</a>
            <a href="/admin/events"     class="admin__nav-link<?= $activeNav === 'events' ? ' is-active' : '' ?>">Events &amp; Activities</a>
            <a href="/admin/impact"     class="admin__nav-link<?= $activeNav === 'impact' ? ' is-active' : '' ?>">Impact Indicators</a>

            <p class="admin__nav-group">Engagement</p>
            <a href="/admin/enquiries/contact"     class="admin__nav-link<?= $activeNav === 'enquiries' ? ' is-active' : '' ?>">Contact Enquiries</a>
            <a href="/admin/enquiries/partnership" class="admin__nav-link<?= $activeNav === 'enquiries' ? ' is-active' : '' ?>">Partnership Enquiries</a>

            <p class="admin__nav-group">Administration</p>
            <?php if ($auth->hasRole('super_admin', 'admin')): ?>
                <a href="/admin/users"         class="admin__nav-link<?= $activeNav === 'users' ? ' is-active' : '' ?>">Users &amp; Roles</a>
                <a href="/admin/system/audit"  class="admin__nav-link<?= $activeNav === 'audit' ? ' is-active' : '' ?>">Audit Log</a>
            <?php endif; ?>
        </nav>

        <div class="admin__sidebar-foot">
            <a href="/" target="_blank" rel="noopener" class="admin__nav-link">↗ View public site</a>
        </div>
    </aside>

    <!-- Main -->
    <div class="admin__main">
        <header class="admin__topbar">
            <button class="admin__burger" type="button" aria-controls="admin-sidebar" aria-expanded="false" aria-label="Toggle sidebar">
                <span></span><span></span><span></span>
            </button>

            <h1 class="admin__page-title"><?= e($title) ?></h1>

            <div class="admin__user">
                <a href="/admin/profile" class="admin__user-name" title="My profile">
                    <?= e($user['name'] ?? 'User') ?>
                </a>
                <span class="admin__user-role"><?= e(implode(', ', $user['roles'] ?? [])) ?></span>
                <form method="post" action="/admin/logout" class="admin__logout">
                    <?= $csrf->field() ?>
                    <button type="submit" class="btn btn--sm btn--ghost-light">Sign out</button>
                </form>
            </div>
        </header>

        <?php if ($breadcrumbs): ?>
            <nav class="admin__breadcrumbs" aria-label="Breadcrumb">
                <ol>
                    <?php $last = count($breadcrumbs) - 1; foreach ($breadcrumbs as $i => $bc): ?>
                        <li>
                            <?php if ($i < $last && !empty($bc['url'])): ?>
                                <a href="<?= e($bc['url']) ?>"><?= e($bc['label']) ?></a>
                            <?php else: ?>
                                <span aria-current="page"><?= e($bc['label']) ?></span>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ol>
            </nav>
        <?php endif; ?>

        <main class="admin__content" id="main">
            <?php require $viewPath; ?>
        </main>
    </div>
</div>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
<script src="<?= e(asset('assets/js/admin.js')) ?>" defer></script>
</body>
</html>