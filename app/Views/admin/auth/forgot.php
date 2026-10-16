<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title><?= e($title ?? 'Reset password') ?> — MWAMBE Admin</title>
    <link rel="icon" type="image/png" href="<?= e(asset('assets/img/mwambe-logo.png')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="auth">
    <main class="auth__panel" role="main">
        <img src="<?= e(asset('assets/img/mwambe-logo.png')) ?>" alt="MWAMBE Agency" class="auth__logo">

        <?php if (!empty($done) || !empty($sent)): ?>
            <h1 class="auth__title">Check your email</h1>
            <p class="auth__subtitle">
                If <strong><?= e($email) ?></strong> is registered, a reset link has been sent.
                The link expires in 60 minutes and can only be used once.
            </p>
            <p class="auth__footnote">
                <a href="/admin/login">← Back to sign in</a>
            </p>
        <?php else: ?>
            <h1 class="auth__title">Reset your password</h1>
            <p class="auth__subtitle">Enter your email and we'll send a reset link.</p>

            <?php if (!empty($error)): ?>
                <div class="alert alert--error" role="alert"><?= e($error) ?></div>
            <?php endif; ?>

            <form method="post" action="/admin/forgot-password" class="form" novalidate>
                <?= $csrf->field() ?>
                <div class="form__row">
                    <label for="email" class="form__label">Email address</label>
                    <input type="email" id="email" name="email" class="form__input" required autofocus
                           value="<?= e($email) ?>" autocomplete="username">
                </div>
                <button type="submit" class="btn btn--primary btn--block">Send reset link</button>
            </form>

            <p class="auth__footnote">
                <a href="/admin/login">← Back to sign in</a>
            </p>
        <?php endif; ?>
    </main>
</body>
</html>