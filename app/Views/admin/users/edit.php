<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="alert alert--error" role="alert">
        <p><strong>Please fix:</strong></p>
        <ul><?php foreach ($errors as $m): ?><li><?= e($m) ?></li><?php endforeach; ?></ul>
    </div>
<?php endif; ?>

<form method="post"
      action="<?= !empty($user['id']) ? '/admin/users/' . (int) $user['id'] . '/edit' : '/admin/users' ?>"
      class="form form--admin" novalidate>
    <?= $csrf->field() ?>

    <fieldset class="form__group">
        <legend>User details</legend>

        <div class="form__grid">
            <div class="form__row">
                <label for="name" class="form__label">Full name <span class="form__required">*</span></label>
                <input type="text" id="name" name="name" class="form__input" required
                       value="<?= e($user['name']) ?>" maxlength="120">
            </div>
            <div class="form__row">
                <label for="email" class="form__label">Email address <span class="form__required">*</span></label>
                <input type="email" id="email" name="email" class="form__input" required
                       value="<?= e($user['email']) ?>" maxlength="190" autocomplete="off">
            </div>
        </div>

        <?php if (empty($user['id'])): ?>
            <div class="form__row">
                <label for="password" class="form__label">Initial password <span class="form__required">*</span></label>
                <input type="password" id="password" name="password" class="form__input" required
                       minlength="12" autocomplete="new-password">
                <p class="form__hint">Minimum 12 characters. The user should change this after first login.</p>
            </div>
        <?php else: ?>
            <p class="form__hint">
                To change a password, use <strong>Send password reset email</strong> below — the user will set
                a new password themselves.
            </p>
        <?php endif; ?>

        <?php if (!empty($user['id'])): ?>
            <div class="form__row form__row--check">
                <input type="checkbox" id="is_active" name="is_active" value="1"
                       <?= !empty($user['is_active']) ? 'checked' : '' ?>>
                <label for="is_active">Account is active</label>
            </div>
        <?php endif; ?>
    </fieldset>

    <fieldset class="form__group">
        <legend>Roles</legend>
        <p class="form__hint">Assign at least one role. Only a Super Administrator can grant administrative roles.</p>
        <div class="roles-grid">
            <?php foreach ($allRoles as $r): ?>
                <label class="roles-grid__item">
                    <input type="checkbox" name="roles[]" value="<?= e($r['slug']) ?>"
                           <?= in_array($r['slug'], $user['roles'] ?? [], true) ? 'checked' : '' ?>>
                    <span>
                        <strong><?= e($r['name']) ?></strong>
                        <small><?= e($r['slug']) ?></small>
                    </span>
                </label>
            <?php endforeach; ?>
        </div>
    </fieldset>

    <div class="form__actions">
        <a href="/admin/users" class="btn btn--ghost-light">Cancel</a>
        <button type="submit" class="btn btn--primary">Save</button>
    </div>
</form>

<?php if (!empty($user['id'])): ?>
<div class="admin__panel">
    <h2>Account actions</h2>
    <div class="button-row">
        <form method="post" action="/admin/users/<?= (int) $user['id'] ?>/send-reset">
            <?= $csrf->field() ?>
            <button type="submit" class="btn btn--sm btn--primary"
                    onclick="return confirm('Send a password reset email to <?= e($user['email']) ?>?');">
                Send password reset email
            </button>
        </form>

        <form method="post" action="/admin/users/<?= (int) $user['id'] ?>/force-logout">
            <?= $csrf->field() ?>
            <button type="submit" class="btn btn--sm btn--ghost-light"
                    onclick="return confirm('Revoke all active sessions for this user? They will be logged out immediately.');">
                Force logout (revoke all sessions)
            </button>
        </form>
    </div>
</div>
<?php endif; ?>