<article>
    <header class="page-hero page-hero--editorial">
        <?php if (!empty($article['media_filename'])): ?>
            <div class="page-hero__bg" aria-hidden="true">
                <img src="<?= e(asset('uploads/' . $article['media_filename'])) ?>"
                     alt="<?= e($article['media_alt'] ?? '') ?>">
            </div>
        <?php endif; ?>
        <div class="page-hero__geo" aria-hidden="true"><span class="page-hero__arc"></span></div>
        <div class="container page-hero__inner">
            <p class="page-hero__eyebrow">
                <?php if (!empty($article['category_name'])): ?>
                    <?= e($article['category_name']) ?> ·
                <?php endif; ?>
                <time datetime="<?= e($article['published_at']) ?>"><?= e(date('d M Y', strtotime($article['published_at']))) ?></time>
            </p>
            <h1 class="page-hero__headline"><?= e($article['title']) ?></h1>
            <?php if (!empty($article['excerpt'])): ?>
                <p class="page-hero__lede"><?= e($article['excerpt']) ?></p>
            <?php endif; ?>
        </div>
    </header>

    <section class="section">
        <div class="container container--narrow">
            <div class="prose"><?= nl2br(e($article['body'])) ?></div>

            <?php if (!empty($article['author_name'])): ?>
                <p class="article-meta">By <?= e($article['author_name']) ?></p>
            <?php endif; ?>

            <p class="article-back">
                <a href="/news">← Back to all news</a>
            </p>
        </div>
    </section>
</article>