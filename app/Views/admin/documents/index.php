<?php if (!empty($flash)): ?>
    <div class="alert alert--success" role="status"><?= e($flash) ?></div>
<?php endif; ?>

<div class="admin__panel">
    <h2>Upload a document</h2>
    <form method="post" action="/admin/documents" enctype="multipart/form-data" class="form form--admin">
        <?= $csrf->field() ?>

        <div class="form__grid">
            <div class="form__row">
                <label for="title" class="form__label">Title <span class="form__required">*</span></label>
                <input type="text" id="title" name="title" class="form__input" required maxlength="190">
            </div>
            <div class="form__row">
                <label for="category" class="form__label">Category</label>
                <input type="text" id="category" name="category" class="form__input" maxlength="80"
                       placeholder="e.g. Reports, Policies, Programme briefs">
            </div>
        </div>

        <div class="form__row">
            <label for="description" class="form__label">Description</label>
            <textarea id="description" name="description" class="form__textarea" rows="3"></textarea>
        </div>

        <div class="form__row">
            <label for="document" class="form__label">File <span class="form__required">*</span>
                <span class="form__hint">PDF, DOC, DOCX, XLS, XLSX — max 20 MB</span>
            </label>
            <input type="file" id="document" name="document" class="form__input" required
                   accept=".pdf,.doc,.docx,.xls,.xlsx">
        </div>

        <div class="form__row form__row--check">
            <input type="checkbox" id="is_public" name="is_public" value="1" checked>
            <label for="is_public">Make available for public download</label>
        </div>

        <div class="form__actions">
            <button type="submit" class="btn btn--primary">Upload</button>
        </div>
    </form>
</div>

<div class="admin__panel">
    <h2>Documents</h2>
    <?php if (empty($documents)): ?>
        <p class="admin__empty">No documents uploaded yet.</p>
    <?php else: ?>
        <table class="admin__table">
            <thead>
                <tr>
                    <th>Title</th>
                    <th>Category</th>
                    <th>File</th>
                    <th>Size</th>
                    <th>Public</th>
                    <th>Downloads</th>
                    <th>Uploaded</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($documents as $d): ?>
                    <tr>
                        <td><?= e($d['title']) ?></td>
                        <td><?= e($d['category'] ?? '—') ?></td>
                        <td><code><?= e($d['original_name']) ?></code></td>
                        <td><?= number_format($d['size_bytes'] / 1024, 0) ?> KB</td>
                        <td><?= (int) $d['is_public'] === 1 ? 'Yes' : 'No' ?></td>
                        <td><?= (int) $d['download_count'] ?></td>
                        <td><?= e(date('d M Y', strtotime($d['created_at']))) ?></td>
                        <td>
                            <form method="post" action="/admin/documents/<?= (int) $d['id'] ?>/delete"
                                  onsubmit="return confirm('Delete this document permanently?');">
                                <?= $csrf->field() ?>
                                <button type="submit" class="btn btn--sm">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>