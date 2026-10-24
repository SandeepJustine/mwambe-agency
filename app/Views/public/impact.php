<!-- Verified impact indicators -->
<?php if (empty($indicators)): ?>
    <section class="section" data-reveal>
        <div class="container container--narrow">
            <div class="callout callout--info">
                <h2 class="callout__title">Verified impact figures are being finalised.</h2>
                <p>
                    MWAMBE is currently in the process of verifying and documenting its reach and impact figures.
                    This page will publish those figures once they have been reviewed and confirmed.
                    In the meantime, please <a href="/contact">contact us</a> for information about our work.
                </p>
            </div>
        </div>
    </section>
<?php else: ?>
    <section class="section impact-results" data-reveal>
        <div class="container">
            <header class="section__header">
                <p class="eyebrow">Verified results</p>
                <h2 class="section__title">Progress you can measure.</h2>
                <p class="section__lede">These figures have been reviewed and approved for publication.</p>
            </header>
            <div class="grid grid--3 impact-results__grid">
                <?php foreach ($indicators as $ind): ?>
                    <?php
                    $indicatorLabel = strtolower((string) ($ind['label'] ?? ''));
                    $indicatorIcon = str_contains($indicatorLabel, 'young') || str_contains($indicatorLabel, 'youth')
                        ? 'icon-youth'
                        : (str_contains($indicatorLabel, 'sme') || str_contains($indicatorLabel, 'enterprise')
                            ? 'icon-enterprise'
                            : 'icon-community');
                    ?>
                    <article class="stat-block stat-block--impact">
                        <span class="stat-block__icon" aria-hidden="true">
                            <svg><use href="#<?= e($indicatorIcon) ?>"></use></svg>
                        </span>
                        <p class="stat-block__value"><?= e($ind['value']) ?><span class="stat-block__unit"><?= e($ind['unit'] ?? '') ?></span></p>
                        <p class="stat-block__label"><?= e($ind['label']) ?></p>
                        <?php if (!empty($ind['description'])): ?>
                            <p class="stat-block__desc"><?= e($ind['description']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($ind['reporting_period'])): ?>
                            <p class="stat-block__meta"><?= e($ind['reporting_period']) ?></p>
                        <?php endif; ?>
                    </article>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
<?php endif; ?>

<section class="section section--alt impact-stories" data-reveal aria-labelledby="impact-stories-title">
    <div class="container">
        <header class="section__header">
            <p class="eyebrow">People &amp; progress</p>
            <h2 class="section__title" id="impact-stories-title">Impact stories.</h2>
            <p class="section__lede">Experiences and outcomes from the people, enterprises and communities connected to our work.</p>
        </header>

        <?php if (empty($impactStories)): ?>
            <div class="callout callout--info">
                <h3 class="callout__title">Impact stories are on the way.</h3>
                <p>Stories will appear here when they are published in the <strong>Impact Story</strong> category.</p>
            </div>
        <?php else: ?>
            <div class="grid grid--3">
                <?php foreach ($impactStories as $story): ?>
                    <article class="card card--article" data-reveal>
                        <?php if (!empty($story['media_filename'])): ?>
                            <img
                                src="<?= e(asset('uploads/' . $story['media_filename'])) ?>"
                                alt="<?= e($story['media_alt'] ?? '') ?>"
                                class="card__image"
                                loading="lazy"
                                decoding="async">
                        <?php endif; ?>
                        <p class="card__meta">
                            Impact Story
                            <?php if (!empty($story['published_at'])): ?>
                                · <time datetime="<?= e($story['published_at']) ?>"><?= e(date('d M Y', strtotime($story['published_at']))) ?></time>
                            <?php endif; ?>
                        </p>
                        <h3 class="card__title">
                            <a href="/news/<?= e($story['slug']) ?>"><?= e($story['title']) ?></a>
                        </h3>
                        <?php if (!empty($story['excerpt'])): ?>
                            <p><?= e($story['excerpt']) ?></p>
                        <?php endif; ?>
                        <a class="impact-story__link" href="/news/<?= e($story['slug']) ?>">
                            Read story <span aria-hidden="true">→</span>
                        </a>
                    </article>
                <?php endforeach; ?>
            </div>
            <p class="section__actions">
                <a class="btn btn--primary" href="/news?category=impact-story">More impact stories</a>
            </p>
        <?php endif; ?>
    </div>
</section>