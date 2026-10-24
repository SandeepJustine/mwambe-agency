<?php
/** @var \Mwambe\Core\Application $app */
/** @var string $title */
$title = $title ?? 'MWAMBE Agency';
?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <title><?= e($title) ?></title>
    <meta name="description" content="MWAMBE Agency — Connecting People, Skills, Enterprises and Opportunities for Resilient Communities. Lilongwe, Malawi.">

    <link rel="icon" type="image/png" href="<?= e(asset('assets/img/mwambe-logo.png')) ?>">
    <link rel="preload" as="image" href="<?= e(asset('assets/img/mwambe-logo.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="public-site">

<svg xmlns="http://www.w3.org/2000/svg" class="icon-definitions" aria-hidden="true">
    <symbol id="icon-youth" viewBox="0 0 24 24">
        <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7.8a4 4 0 0 1 0 7.6M20 21v-2a4 4 0 0 0-3-3.87"/>
    </symbol>
    <symbol id="icon-enterprise" viewBox="0 0 24 24">
        <path d="M3 21h18M5 21V7l7-4 7 4v14M9 21v-5h6v5M9 9h.01M15 9h.01M9 12h.01M15 12h.01"/>
    </symbol>
    <symbol id="icon-agriculture" viewBox="0 0 24 24">
        <path d="M20 4c-7 0-12 2-14 7a5 5 0 0 0 5 7c5-2 7-7 9-14ZM3 21c2-5 6-8 11-10"/>
        <path d="M5 5c1 0 2 .5 3 1.5M3 10c1.5 0 2.5.5 3.5 1.5"/>
    </symbol>
    <symbol id="icon-community" viewBox="0 0 24 24">
        <path d="M3 20h18M5 20v-7l7-5 7 5v7M9 20v-5h6v5M3 9l9-6 9 6"/>
    </symbol>
    <symbol id="icon-pin" viewBox="0 0 24 24">
        <path d="M20 10c0 5-8 12-8 12S4 15 4 10a8 8 0 1 1 16 0Z"/>
        <circle cx="12" cy="10" r="2.5"/>
    </symbol>
    <symbol id="icon-envelope" viewBox="0 0 24 24">
        <rect x="3" y="5" width="18" height="14" rx="2"/>
        <path d="m3 7 9 6 9-6"/>
    </symbol>
    <symbol id="icon-phone" viewBox="0 0 24 24">
        <path d="M22 16.9v3a2 2 0 0 1-2.2 2A19.8 19.8 0 0 1 3.1 5.2 2 2 0 0 1 5.1 3h3a2 2 0 0 1 2 1.7l.5 3a2 2 0 0 1-.6 1.8L8.1 11a16 16 0 0 0 4.9 4.9l1.5-1.9a2 2 0 0 1 1.8-.6l3 .5a2 2 0 0 1 1.7 2Z"/>
    </symbol>
    <symbol id="icon-eye" viewBox="0 0 24 24">
        <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7-10-7-10-7Z"/>
        <circle cx="12" cy="12" r="3"/>
    </symbol>
    <symbol id="icon-target" viewBox="0 0 24 24">
        <circle cx="12" cy="12" r="9"/>
        <circle cx="12" cy="12" r="5"/>
        <circle cx="12" cy="12" r="1"/>
    </symbol>
    <symbol id="icon-search" viewBox="0 0 24 24">
        <circle cx="10.8" cy="10.8" r="6.8"/>
        <path d="m16 16 5 5M10.8 7.5v6.6M7.5 10.8h6.6"/>
    </symbol>
    <symbol id="icon-book-open" viewBox="0 0 24 24">
        <path d="M12 7v14M12 7C9.5 4.8 6.5 4 3 4v14c3.5 0 6.5.8 9 3m0-14c2.5-2.2 5.5-3 9-3v14c-3.5 0-6.5.8-9 3"/>
    </symbol>
    <symbol id="icon-link" viewBox="0 0 24 24">
        <path d="m10 13.5 4-4M8.5 15 6 17.5a4 4 0 0 1-5.5-5.8l4-4a4 4 0 0 1 5.7 0m5.3 1.3 2.5-2.5a4 4 0 0 1 5.5 5.8l-4 4a4 4 0 0 1-5.7 0"/>
    </symbol>
    <symbol id="icon-action" viewBox="0 0 24 24">
        <path d="M7 11V5a2 2 0 0 1 4 0v5-7a2 2 0 0 1 4 0v7-5a2 2 0 0 1 4 0v7l1-2a2 2 0 0 1 3 2l-3 8a5 5 0 0 1-5 3h-3a6 6 0 0 1-5-3l-4-5a2 2 0 0 1 3-3l3 3"/>
    </symbol>
    <symbol id="icon-chart" viewBox="0 0 24 24">
        <path d="M3 3v18h18M8 15l4-4 3 3 6-7M17 7h4v4"/>
    </symbol>
    <symbol id="icon-equality" viewBox="0 0 24 24">
        <path d="M12 3v18M5 6h14M7 6l-4 7h8L7 6Zm10 0-4 7h8l-4-7ZM5 21h14"/>
    </symbol>
    <symbol id="icon-integrity" viewBox="0 0 24 24">
        <path d="M12 22s8-4 8-11V5l-8-3-8 3v6c0 7 8 11 8 11Z"/>
        <path d="m8.5 12 2.3 2.3 4.8-5"/>
    </symbol>
    <symbol id="icon-collaboration" viewBox="0 0 24 24">
        <path d="M16 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2M10 11a4 4 0 1 0 0-8 4 4 0 0 0 0 8Zm7-7.8a4 4 0 0 1 0 7.6M20 21v-2a4 4 0 0 0-3-3.87"/>
    </symbol>
    <symbol id="icon-innovation" viewBox="0 0 24 24">
        <path d="M9 18h6m-5 4h4m-5-7a7 7 0 1 1 6 0c-.8.6-1 1.4-1 3h-4c0-1.6-.2-2.4-1-3Z"/>
        <path d="M12 2v2M4.9 4.9l1.4 1.4m11.4-1.4-1.4 1.4"/>
    </symbol>
    <symbol id="icon-sustainability" viewBox="0 0 24 24">
        <path d="M20 4c-7 0-12 2-14 7a5 5 0 0 0 5 7c5-2 7-7 9-14ZM3 21c2-5 6-8 11-10"/>
        <path d="M20 4c-1 4-4 8-9 11"/>
    </symbol>
</svg>

<a class="skip-link" href="#main">Skip to content</a>

<?php require BASE_PATH . '/app/Views/components/header.php'; ?>

<main id="main">
    <?php require $viewPath; ?>
</main>

<?php require BASE_PATH . '/app/Views/components/footer.php'; ?>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>