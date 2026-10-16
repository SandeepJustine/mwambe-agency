<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Set new password') ?> — MWAMBE Admin</title>
    <link rel="icon" type="image/png" href="<?= e(asset('assets/img/mwambe-logo.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="auth">
    <main class="auth__panel" role="main">
        <img src="<?= e(asset('assets/img/mwambe-logo.png')) ?>" alt="MWAMBE Agency" class="auth__logo">

        <?php if (!empty($done)): ?>
            <h1 class="auth__title">Password updated</h1>
            <p class="auth__subtitle">Your password has been changed and all active sessions have been signed out.</p>
            <p class="auth__footnote">
                <a class="btn btn--primary" href="/admin/login">Sign in with your new password</a>
            </p>
        <?php elseif (empty($valid)): ?>
            <h1 class="auth__title">Link not valid</h1>
            <p class="auth__subtitle"><?= e($error ?? 'This reset link is invalid or has expired.') ?></p>
            <p class="auth__footnote">
                <a href="/admin/forgot-password">Request a new link</a>
            </p>
        <?php else: ?>
            <h1 class="auth__title">Set a new password</h1>
            <p class="auth__subtitle">Choose a strong password of at least 12 characters.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert--error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/admin/reset-password" class="form" novalidate>
                <?= $csrf->field() ?>
                <input type="hidden" name="token" value="<?= e($token) ?>">

                <div class="form__row">
                    <label for="password" class="form__label">New password</label>
                    <input type="password" id="password" name="password" class="form__input"
                           required minlength="12" autocomplete="new-password" autofocus>
                </div>
                <div class="form__row">
                    <label for="password_confirm" class="form__label">Confirm new password</label>
                    <input type="password" id="password_confirm" name="password_confirm" class="form__input"
                           required minlength="12" autocomplete="new-password">
                </div>
                <button type="submit" class="btn btn--primary btn--block">Set new password</button>
            </form>

            <p class="auth__footnote">
                <a href="/admin/login">← Back to sign in</a>
            </p>
        <?php endif; ?>
    </main>
</body>
</html>