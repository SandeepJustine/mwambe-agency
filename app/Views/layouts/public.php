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
<body>

<?php require BASE_PATH . '/app/Views/components/header.php'; ?>

<main id="main">
    <?php require $viewPath; ?>
</main>

<?php require BASE_PATH . '/app/Views/components/footer.php'; ?>

<script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>