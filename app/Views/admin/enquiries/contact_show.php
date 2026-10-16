<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<?php if (!empty($enquiry['email_error'])): ?>
    <div class="alert alert--error">
        <strong>Email delivery failed:</strong> <?= e($enquiry['email_error']) ?>
        <form method="post" action="/admin/enquiries/contact/<?= (int) $enquiry['id'] ?>/retry-email" style="display:inline;margin-left:1rem">
            <?= $csrf->field() ?>
            <button type="submit" class="btn btn--sm">Retry email</button>
        </form>
    </div>
<?php endif; ?>

<div class="admin__panel">
    <header class="admin__panel-head">
        <h2>Enquiry <code><?= e($enquiry['reference']) ?></code></h2>
        <p class="admin__panel-hint">
            Received <time datetime="<?= e($enquiry['created_at']) ?>"><?= e(date('l, j F Y — H:i', strtotime($enquiry['created_at']))) ?></time>
        </p>
    </header>

    <dl class="deflist">
        <dt>Name</dt>      <dd><?= e($enquiry['name']) ?></dd>
        <dt>Email</dt>     <dd><a href="mailto:<?= e($enquiry['email']) ?>"><?= e($enquiry['email']) ?></a></dd>
        <?php if ($enquiry['phone']): ?><dt>Phone</dt><dd><a href="tel:<?= e($enquiry['phone']) ?>"><?= e($enquiry['phone']) ?></a></dd><?php endif; ?>
        <dt>Subject</dt>   <dd><?= e($enquiry['subject'] ?: '—') ?></dd>
        <dt>Message</dt>   <dd class="deflist__message"><?= nl2br(e($enquiry['message'])) ?></dd>
        <dt>IP</dt>        <dd><code><?= e($enquiry['ip_address'] ?? '—') ?></code></dd>
    </dl>
</div>

<div class="admin__panel">
    <h2>Status &amp; handling</h2>
    <form method="post" action="/admin/enquiries/contact/<?= (int) $enquiry['id'] ?>/edit" class="form form--admin">
        <?= $csrf->field() ?>
        <div class="form__row">
            <label for="status" class="form__label">Status</label>
            <select id="status" name="status" class="form__select">
                <?php foreach (['new' => 'New', 'in_review' => 'In review', 'assigned' => 'Assigned', 'responded' => 'Responded', 'resolved' => 'Resolved', 'archived' => 'Archived'] as $v => $lbl): ?>
                    <option value="<?= e($v) ?>" <?= $enquiry['status'] === $v ? 'selected' : '' ?>><?= e($lbl) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form__row">
            <label for="note" class="form__label">Add internal note</label>
            <textarea id="note" name="note" class="form__textarea" rows="3" placeholder="Internal notes — not shown to the sender."></textarea>
        </div>
        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Save</button>
        </div>
    </form>
</div>

<?php if (!empty($notes)): ?>
<div class="admin__panel">
    <h2>Internal notes</h2>
    <ul class="noteslist">
        <?php foreach ($notes as $n): ?>
            <li class="noteslist__item">
                <p class="noteslist__meta">
                    <strong><?= e($n['user_name'] ?? 'System') ?></strong>
                    · <time datetime="<?= e($n['created_at']) ?>"><?= e(date('d M Y H:i', strtotime($n['created_at']))) ?></time>
                </p>
                <p class="noteslist__body"><?= nl2br(e($n['body'])) ?></p>
            </li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<p><a href="/admin/enquiries/contact">← Back to all contact enquiries</a></p>