<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Page not found — MWAMBE Agency</title>
    <link rel="icon" type="image/png" href="<?= e(asset('assets/img/mwambe-logo.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body>
<main class="error-page" role="main">
    <div class="container container--narrow error-page__inner">
        <img src="<?= e(asset('assets/img/mwambe-logo.png')) ?>" alt="MWAMBE Agency" width="120" height="120">
        <p class="error-page__code">404</p>
        <h1 class="error-page__title">Page not found.</h1>
        <p class="error-page__lede">The page you're looking for doesn't exist or has moved.</p>
        <p class="error-page__actions">
            <a class="btn btn--primary" href="/">Return home</a>
            <a class="btn btn--ghost" href="/contact">Contact us</a>
        </p>
    </div>
</main>
</body>
</html>