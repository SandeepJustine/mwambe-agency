<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>
<?php if (!empty($error)): ?>
    <div class="alert alert--error" role="alert"><?= e($error) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <h2>Profile</h2>
    <dl class="deflist">
        <dt>Name</dt>   <dd><?= e($user['name']) ?></dd>
        <dt>Email</dt>  <dd><?= e($user['email']) ?></dd>
        <dt>Roles</dt>  <dd><?= e(implode(', ', $user['roles'] ?? [])) ?></dd>
    </dl>
</div>

<div class="admin__panel">
    <h2>Change password</h2>
    <form method="post" action="/admin/profile/password" class="form form--admin" novalidate>
        <?= $csrf->field() ?>
        <div class="form__row">
            <label for="current_password" class="form__label">Current password</label>
            <input type="password" id="current_password" name="current_password" class="form__input"
                   required autocomplete="current-password">
        </div>
        <div class="form__row">
            <label for="new_password" class="form__label">New password</label>
            <input type="password" id="new_password" name="new_password" class="form__input"
                   required minlength="12" autocomplete="new-password">
            <p class="form__hint">Minimum 12 characters. All other sessions will be signed out.</p>
        </div>
        <div class="form__row">
            <label for="new_password_confirm" class="form__label">Confirm new password</label>
            <input type="password" id="new_password_confirm" name="new_password_confirm" class="form__input"
                   required minlength="12" autocomplete="new-password">
        </div>
        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Change password</button>
        </div>
    </form>
</div>