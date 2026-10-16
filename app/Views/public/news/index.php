<section class="page-hero page-hero--minimal">
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Updates</p>
        <h1 class="page-hero__headline">News, insights and activities.</h1>
        <p class="page-hero__lede">Updates on MWAMBE Agency's work with young people, enterprises and communities.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <?php if (empty($articles)): ?>
            <div class="callout callout--info">
                <h2 class="callout__title">No articles published yet.</h2>
                <p>News and activities will appear here as they are published.</p>
            </div>
        <?php else: ?>
            <div class="grid grid--3">
                <?php foreach ($articles as $a): ?>
                    <article class="card card--article" data-reveal>
                        <?php if (!empty($a['media_filename'])): ?>
                            <img src="<?= e(asset('uploads/' . $a['media_filename'])) ?>"
                                 alt="<?= e($a['media_alt'] ?? '') ?>" class="card__image" loading="lazy">
                        <?php endif; ?>
                        <p class="card__meta">
                            <?php if (!empty($a['category_name'])): ?>
                                <?= e($a['category_name']) ?> ·
                            <?php endif; ?>
                            <time datetime="<?= e($a['published_at']) ?>"><?= e(date('d M Y', strtotime($a['published_at']))) ?></time>
                        </p>
                        <h2 class="card__title">
                            <a href="/news/<?= e($a['slug']) ?>"><?= e($a['title']) ?></a>
                        </h2>
                        <p><?= e($a['excerpt']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>

            <?php $pages = (int) ceil($total / $perPage); if ($pages > 1): ?>
                <nav class="pagination" aria-label="Pagination">
                    <?php for ($i = 1; $i <= $pages; $i++): ?>
                        <?php if ($i === $page): ?>
                            <span class="is-current" aria-current="page"><?= $i ?></span>
                        <?php else: ?>
                            <a href="?page=<?= $i ?><?= $category ? '&category=' . e($category) : '' ?>"><?= $i ?></a>
                        <?php endif; ?>
                    <?php endfor; ?>
                </nav>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</section>