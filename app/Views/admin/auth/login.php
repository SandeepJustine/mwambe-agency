<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>Sign in — MWAMBE Admin</title>
    <link rel="icon" type="image/png" href="<?= e(asset('assets/img/mwambe-logo.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin-dashboard.css')) ?>">
</head>
<body class="auth">
    <main class="auth__panel" role="main">
        <img src="<?= e(asset('assets/img/mwambe-logo.png')) ?>" alt="MWAMBE Agency" class="auth__logo">

        <h1 class="auth__title">Sign in to the admin dashboard</h1>
        <p class="auth__subtitle">Authorised staff only.</p>

        <?php if ($error): ?>
            <div class="alert alert--error" role="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/admin/login" class="form" novalidate>
            <?= $csrf->field() ?>

            <div class="form__row">
                <label for="email" class="form__label">Email address</label>
                <input
                    type="email" id="email" name="email" class="form__input"
                    value="<?= e($email ?? '') ?>"
                    autocomplete="username" required autofocus>
            </div>

            <div class="form__row">
                <label for="password" class="form__label">Password</label>
                <input
                    type="password" id="password" name="password" class="form__input"
                    autocomplete="current-password" required>
            </div>

            <button type="submit" class="btn btn--primary btn--block">Sign in</button>
        </form>

        <p class="auth__footnote">
            <a href="/">← Back to MWAMBE Agency</a>
        </p>
    </main>
</body>
</html>