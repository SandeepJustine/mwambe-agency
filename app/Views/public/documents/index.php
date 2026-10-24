<?php
$formatFileSize = static function (int $bytes): string {
    if ($bytes >= 1_048_576) {
        return number_format($bytes / 1_048_576, 1) . ' MB';
    }

    return number_format(max(1, (int) round($bytes / 1024))) . ' KB';
};
?>
<section class="page-hero page-hero--minimal">
    <div class="page-hero__bg" aria-hidden="true">
        <img
            src="<?= e(asset('uploads/2026/10/2369d12bad240e561d82423f.jpg')) ?>"
            alt=""
            width="1000" height="667"
            fetchpriority="high">
    </div>
    <div class="page-hero__geo" aria-hidden="true"><span class="page-hero__arc"></span></div>
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Resources</p>
        <h1 class="page-hero__headline">Documents &amp; publications.</h1>
        <p class="page-hero__lede">Browse public resources and publications from MWAMBE Agency.</p>
    </div>
</section>

<section class="section document-library">
    <div class="container">
        <form method="get" action="/documents" class="document-filters" role="search">
            <div class="document-filters__search">
                <label for="document-query" class="visually-hidden">Search documents</label>
                <input
                    class="search-form__input"
                    type="search"
                    id="document-query"
                    name="q"
                    value="<?= e($query) ?>"
                    placeholder="Search documents…">
            </div>
            <div class="document-filters__category">
                <label for="document-category" class="visually-hidden">Filter by category</label>
                <select class="form__select" id="document-category" name="category">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $documentCategory): ?>
                        <option value="<?= e($documentCategory) ?>"<?= $category === $documentCategory ? ' selected' : '' ?>>
                            <?= e($documentCategory) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button class="btn btn--primary" type="submit">Search resources</button>
            <?php if ($query !== '' || $category !== ''): ?>
                <a class="document-filters__reset" href="/documents">Clear filters</a>
            <?php endif; ?>
        </form>

        <?php if ($query !== '' || $category !== ''): ?>
            <p class="document-results-count">
                <?= (int) $total ?> resource<?= $total === 1 ? '' : 's' ?> found
                <?php if ($query !== ''): ?> for “<?= e($query) ?>”<?php endif; ?>
                <?php if ($category !== ''): ?> in <?= e($category) ?><?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if (empty($documents)): ?>
            <div class="callout callout--info document-library__empty">
                <?php if ($query !== '' || $category !== ''): ?>
                    <h2 class="callout__title">No matching documents.</h2>
                    <p>Try a different search or category, or <a href="/documents">browse all resources</a>.</p>
                <?php else: ?>
                    <h2 class="callout__title">Resources are being prepared.</h2>
                    <p>Public documents will appear here when they are published. For information, please <a href="/contact">contact us</a>.</p>
                <?php endif; ?>
            </div>
        <?php else: ?>
            <div class="document-grid">
                <?php foreach ($documents as $document): ?>
                    <?php $extension = strtoupper(pathinfo((string) $document['original_name'], PATHINFO_EXTENSION)); ?>
                    <article class="document-card">
                        <span class="document-card__icon" aria-hidden="true">
                            <svg><use href="#icon-book-open"></use></svg>
                        </span>
                        <div class="document-card__content">
                            <div class="document-card__meta">
                                <span class="document-card__type"><?= e($extension ?: 'FILE') ?></span>
                                <?php if (!empty($document['category'])): ?>
                                    <span><?= e($document['category']) ?></span>
                                <?php endif; ?>
                            </div>
                            <h2 class="document-card__title"><?= e($document['title']) ?></h2>
                            <?php if (!empty($document['description'])): ?>
                                <p class="document-card__description"><?= e($document['description']) ?></p>
                            <?php endif; ?>
                            <p class="document-card__details">
                                <?= e($formatFileSize((int) $document['size_bytes'])) ?>
                                <span aria-hidden="true">·</span>
                                Published <?= e(date('d M Y', strtotime($document['created_at']))) ?>
                            </p>
                        </div>
                        <a class="btn btn--primary document-card__download"
                           href="/documents/<?= e($document['slug']) ?>"
                           aria-label="Download <?= e($document['title']) ?>">
                            Download <span aria-hidden="true">↓</span>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php $pages = (int) ceil($total / $perPage); if ($pages > 1): ?>
                <?php
                $paginationQuery = array_filter([
                    'q' => $query,
                    'category' => $category,
                ], static fn(string $value): bool => $value !== '');
                ?>
                <nav class="pagination" aria-label="Document pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="is-current" aria-current="page"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?<?= e(http_build_query(['page' => $i] + $paginationQuery)) ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>
