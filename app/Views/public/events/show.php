<article>
    <header class="page-hero page-hero--editorial">
        <div class="container page-hero__inner">
            <p class="page-hero__eyebrow">Event</p>
            <h1 class="page-hero__headline"><?= e($event['title']) ?></h1>
            <p class="page-hero__lede">
                <time datetime="<?= e($event['starts_at']) ?>">
                    <?= e(date('l, j F Y — H:i', strtotime($event['starts_at']))) ?>
                </time>
                <?php if (!empty($event['location'])): ?>
                    · <?= e($event['location']) ?>
                <?php endif; ?>
            </p>
        </div>
    </header>

    <section class="section">
        <div class="container container--narrow">
            <?php if (!empty($event['summary'])): ?>
                <p class="event-summary"><?= e($event['summary']) ?></p>
            <?php endif; ?>
            <?php if (!empty($event['body'])): ?>
                <div class="prose"><?= nl2br(e($event['body'])) ?></div>
            <?php endif; ?>
        </div>
    </section>
</article>