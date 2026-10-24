<section class="page-hero page-hero--minimal news-hero">
    <div class="page-hero__bg" aria-hidden="true">
        <img
            src="<?= e(asset('uploads/2026/10/2369d12bad240e561d82423f.jpg')) ?>"
            alt=""
            width="1000" height="667"
            fetchpriority="high">
    </div>
    <div class="page-hero__geo" aria-hidden="true"><span class="page-hero__arc"></span></div>
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Updates</p>
        <h1 class="page-hero__headline">News, insights and activities.</h1>
        <p class="page-hero__lede">Updates on MWAMBE Agency's work with young people, enterprises and communities.</p>
    </div>
</section>

<section class="section">
    <div class="container">
        <form method="get" action="/news" class="news-filters" role="search">
            <div class="news-filters__search">
                <label class="visually-hidden" for="news-query">Search news and activities</label>
                <input
                    class="search-form__input"
                    type="search"
                    id="news-query"
                    name="q"
                    value="<?= e($search ?? '') ?>"
                    placeholder="Search news and activities…">
            </div>
            <div class="news-filters__category">
                <label class="visually-hidden" for="news-category">Filter by category</label>
                <select id="news-category" name="category" class="form__select">
                    <option value="">All categories</option>
                    <?php foreach ($categories as $newsCategory): ?>
                        <option value="<?= e($newsCategory['slug']) ?>"<?= ($category ?? '') === $newsCategory['slug'] ? ' selected' : '' ?>>
                            <?= e($newsCategory['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn--primary">Search</button>
            <?php if (!empty($search) || !empty($category)): ?>
                <a class="news-filters__reset" href="/news">Clear filters</a>
            <?php endif; ?>
        </form>

        <?php if (!empty($search) || !empty($category)): ?>
            <p class="news-results-count">
                <?= (int) $total ?> result<?= $total === 1 ? '' : 's' ?>
                <?php if (!empty($search)): ?> for “<?= e($search) ?>”<?php endif; ?>
                <?php if (!empty($category)): ?>
                    <?php foreach ($categories as $newsCategory): ?>
                        <?php if ($newsCategory['slug'] === $category): ?> in <?= e($newsCategory['name']) ?><?php break; endif; ?>
                    <?php endforeach; ?>
                <?php endif; ?>
            </p>
        <?php endif; ?>

        <?php if (empty($articles)): ?>
            <div class="callout callout--info">
                <?php if (!empty($search) || !empty($category)): ?>
                    <h2 class="callout__title">No matching stories found.</h2>
                    <p>Try another search or category, or <a href="/news">browse all news and activities</a>.</p>
                <?php else: ?>
                    <h2 class="callout__title">No articles published yet.</h2>
                    <p>News and activities will appear here as they are published.</p>
                <?php endif; ?>
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
                    <?php
                    $paginationQuery = array_filter([
                        'q' => $search ?? '',
                        'category' => $category ?? '',
                    ], static fn(string $value): bool => $value !== '');
                    ?>
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