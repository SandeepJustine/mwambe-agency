<section class="page-hero page-hero--split">
    <div class="page-hero__bg" aria-hidden="true">
        <img
            src="<?= e(asset('uploads/2026/10/2369d12bad240e561d82423f.jpg')) ?>"
            alt=""
            width="1000" height="667"
            fetchpriority="high">
    </div>
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Programmes &amp; Projects</p>
        <h1 class="page-hero__headline">Structured work with defined outcomes.</h1>
        <p class="page-hero__lede">
            MWAMBE is transitioning from a founder-led opportunity-assistance model
            toward structured programmes with measurable outcomes and stronger institutional partnerships.
        </p>
    </div>
</section>

<section class="section">
    <div class="container">
        <nav class="filters" aria-label="Filter by kind">
            <a href="/our-work/programmes" class="filters__link<?= $currentKind === '' ? ' is-active' : '' ?>">All</a>
            <a href="/our-work/programmes?kind=programme" class="filters__link<?= $currentKind === 'programme' ? ' is-active' : '' ?>">Programmes</a>
            <a href="/our-work/programmes?kind=project" class="filters__link<?= $currentKind === 'project' ? ' is-active' : '' ?>">Projects</a>
        </nav>

        <?php if (empty($programmes)): ?>
            <div class="callout callout--info">
                <h2 class="callout__title">Programmes are being finalised.</h2>
                <p>We are currently structuring our work into formal programmes. Check back soon, or <a href="/contact">contact us</a> for information about current activities.</p>
            </div>
        <?php else: ?>
            <div class="grid grid--3">
                <?php foreach ($programmes as $p): ?>
                    <article class="card card--programme" data-reveal>
                        <?php if (!empty($p['media_filename'])): ?>
                            <img src="<?= e(asset('uploads/' . $p['media_filename'])) ?>"
                                 alt="<?= e($p['media_alt'] ?? '') ?>" class="card__image" loading="lazy">
                        <?php endif; ?>
                        <p class="card__meta">
                            <span class="status status--<?= e($p['status']) ?>"><?= e(str_replace('_', ' ', $p['status'])) ?></span>
                        </p>
                        <h2 class="card__title">
                            <a href="/our-work/programmes/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a>
                        </h2>
                        <p><?= e($p['summary']) ?></p>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>