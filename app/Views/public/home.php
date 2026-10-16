<?php
/** @var array<int,array<string,mixed>> $slides */
?>

<!-- ================= HERO SLIDER (exactly 3 slides) ================= -->
<section
    class="hero-slider"
    data-slider
    data-autoplay-interval="7000"
    aria-roledescription="carousel"
    aria-label="MWAMBE Agency — featured messages">

    <div class="hero-slider__viewport">
        <?php foreach ($slides as $i => $slide): ?>
            <?php
                $isFirst  = $i === 0;
                $layout   = (string) $slide['layout'];
                $mediaUrl = !empty($slide['media_filename'])
                    ? asset('uploads/' . $slide['media_filename'])
                    : null;
                $mediaAlt = (string) ($slide['media_alt'] ?? '');
            ?>
            <article
                class="hero-slide hero-slide--<?= e($layout) ?><?= $isFirst ? ' is-active' : '' ?>"
                role="group"
                aria-roledescription="slide"
                aria-label="<?= $i + 1 ?> of <?= count($slides) ?>"
                <?= $isFirst ? '' : 'aria-hidden="true"' ?>
                data-slide
                data-index="<?= $i ?>">

                <div class="hero-slide__bg" aria-hidden="true">
                    <?php if ($mediaUrl): ?>
                        <img src="<?= e($mediaUrl) ?>" alt="<?= e($mediaAlt) ?>" loading="<?= $isFirst ? 'eager' : 'lazy' ?>">
                    <?php endif; ?>
                </div>

                <div class="hero-slide__geo" aria-hidden="true">
                    <span class="hero-slide__arc"></span>
                    <span class="hero-slide__dot"></span>
                </div>

                <div class="container hero-slide__inner">
                    <div class="hero-slide__content">
                        <?php if (!empty($slide['eyebrow'])): ?>
                            <p class="hero-slide__eyebrow"><?= e($slide['eyebrow']) ?></p>
                        <?php endif; ?>

                        <h1 class="hero-slide__headline"><?= e($slide['headline']) ?></h1>

                        <p class="hero-slide__supporting"><?= e($slide['supporting']) ?></p>

                        <div class="hero-slide__actions">
                            <?php if (!empty($slide['primary_cta_label'])): ?>
                                <a class="btn btn--primary"
                                   href="<?= e($slide['primary_cta_url'] ?: '#') ?>">
                                    <?= e($slide['primary_cta_label']) ?>
                                </a>
                            <?php endif; ?>
                            <?php if (!empty($slide['secondary_cta_label'])): ?>
                                <a class="btn btn--ghost"
                                   href="<?= e($slide['secondary_cta_url'] ?: '#') ?>">
                                    <?= e($slide['secondary_cta_label']) ?>
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <div class="hero-slider__controls container">
        <button class="hero-slider__arrow" type="button" data-slider-prev aria-label="Previous slide">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="15 18 9 12 15 6"/></svg>
        </button>

        <ol class="hero-slider__pagination" role="tablist" aria-label="Choose slide">
            <?php foreach ($slides as $i => $_): ?>
                <li role="presentation">
                    <button
                        class="hero-slider__dot<?= $i === 0 ? ' is-active' : '' ?>"
                        type="button"
                        role="tab"
                        aria-selected="<?= $i === 0 ? 'true' : 'false' ?>"
                        aria-label="Go to slide <?= $i + 1 ?>"
                        data-slider-go="<?= $i ?>"></button>
                </li>
            <?php endforeach; ?>
        </ol>

        <button class="hero-slider__arrow" type="button" data-slider-next aria-label="Next slide">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><polyline points="9 18 15 12 9 6"/></svg>
        </button>
    </div>
</section>

<!-- ================= INTRO ================= -->
<section class="section section--intro" data-reveal>
    <div class="container">
        <div class="section__header">
            <p class="eyebrow">Who We Are</p>
            <h2 class="section__title">Access to an opportunity is only the beginning.</h2>
            <p class="section__lede">
                MWAMBE Agency is a young, Malawi-based organisation working to connect young people,
                skilled professionals, SMEs, cooperatives and community groups to opportunities that
                strengthen skills, livelihoods, enterprise development and community resilience.
            </p>
        </div>

        <div class="grid grid--3">
            <article class="card" data-reveal>
                <h3 class="card__title">Our Vision</h3>
                <p>Build resilient communities where people, enterprises and community groups can access opportunities, develop practical capacity and create sustainable livelihoods.</p>
            </article>
            <article class="card" data-reveal>
                <h3 class="card__title">Our Mission</h3>
                <p>Connect skilled people, SMEs, cooperatives and community groups to opportunities, partnerships and practical capacity-building initiatives.</p>
            </article>
            <article class="card" data-reveal>
                <h3 class="card__title">Our Approach</h3>
                <p>Identify &rarr; Build Capacity &rarr; Connect &rarr; Support Action &rarr; Document Results. We prepare people to compete, participate and create value.</p>
            </article>
        </div>
    </div>
</section>

<!-- ================= AREAS OF WORK ================= -->
<section class="section section--alt" data-reveal>
    <div class="container">
        <div class="section__header">
            <p class="eyebrow">What We Do</p>
            <h2 class="section__title">Four areas of work.</h2>
        </div>

        <div class="grid grid--2">
            <article class="feature" data-reveal>
                <span class="feature__mark" aria-hidden="true"></span>
                <h3 class="feature__title">Youth &amp; Professional Development</h3>
                <p>Career coaching, CV and professional positioning, personal branding, employability workshops, mentorship and networking.</p>
            </article>
            <article class="feature" data-reveal>
                <span class="feature__mark" aria-hidden="true"></span>
                <h3 class="feature__title">Entrepreneurship &amp; SME Development</h3>
                <p>Business development, registration guidance, marketing and brand development, digital marketing, research and proposal development.</p>
            </article>
            <article class="feature" data-reveal>
                <span class="feature__mark" aria-hidden="true"></span>
                <h3 class="feature__title">Agriculture &amp; Agribusiness</h3>
                <p>Agribusiness development, agricultural entrepreneurship, youth participation in agriculture, research and climate-resilient livelihoods.</p>
            </article>
            <article class="feature" data-reveal>
                <span class="feature__mark" aria-hidden="true"></span>
                <h3 class="feature__title">Project &amp; Community Development</h3>
                <p>Project planning, community engagement, field operations, research and data collection, reporting and partnership coordination.</p>
            </article>
        </div>
    </div>
</section>

<!-- FEATURED PROGRAMMES -->
<?php if (!empty($featuredPrograms)): ?>
<section class="section section--alt" data-reveal>
    <div class="container">
        <div class="section__header">
            <p class="eyebrow">Programmes &amp; Projects</p>
            <h2 class="section__title">Structured work with defined outcomes.</h2>
        </div>
        <div class="grid grid--3">
            <?php foreach ($featuredPrograms as $p): ?>
                <article class="card card--programme">
                    <p class="card__meta">
                        <span class="status status--<?= e($p['status']) ?>"><?= e(str_replace('_', ' ', $p['status'])) ?></span>
                    </p>
                    <h3 class="card__title"><a href="/programmes/<?= e($p['slug']) ?>"><?= e($p['title']) ?></a></h3>
                    <p><?= e($p['summary']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- VERIFIED IMPACT -->
<?php if (!empty($impactIndicators)): ?>
<section class="section section--dark" data-reveal>
    <div class="container">
        <div class="section__header">
            <p class="eyebrow eyebrow--gold">Our Impact</p>
            <h2 class="section__title">Verified indicators.</h2>
            <p class="section__lede">Only figures confirmed by an authorised administrator appear here.</p>
        </div>
        <div class="grid grid--3">
            <?php foreach ($impactIndicators as $ind): ?>
                <article class="stat-block">
                    <p class="stat-block__value"><?= e($ind['value']) ?><span class="stat-block__unit"><?= e($ind['unit'] ?? '') ?></span></p>
                    <p class="stat-block__label"><?= e($ind['label']) ?></p>
                    <?php if (!empty($ind['description'])): ?>
                        <p class="stat-block__desc"><?= e($ind['description']) ?></p>
                    <?php endif; ?>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<!-- UPCOMING EVENTS -->
<?php if (!empty($upcomingEvents)): ?>
<section class="section" data-reveal>
    <div class="container">
        <div class="section__header">
            <p class="eyebrow">Upcoming</p>
            <h2 class="section__title">Events &amp; activities.</h2>
        </div>
        <ul class="event-list">
            <?php foreach ($upcomingEvents as $ev): ?>
                <li class="event-list__item">
                    <time datetime="<?= e($ev['starts_at']) ?>" class="event-list__date">
                        <span class="event-list__day"><?= e(date('d', strtotime($ev['starts_at']))) ?></span>
                        <span class="event-list__month"><?= e(date('M Y', strtotime($ev['starts_at']))) ?></span>
                    </time>
                    <div class="event-list__body">
                        <h3><a href="/events/<?= e($ev['slug']) ?>"><?= e($ev['title']) ?></a></h3>
                        <?php if (!empty($ev['location'])): ?><p class="event-list__location"><?= e($ev['location']) ?></p><?php endif; ?>
                        <?php if (!empty($ev['summary'])): ?><p><?= e($ev['summary']) ?></p><?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<!-- LATEST NEWS -->
<?php if (!empty($latestNews)): ?>
<section class="section section--alt" data-reveal>
    <div class="container">
        <div class="section__header">
            <p class="eyebrow">News &amp; Insights</p>
            <h2 class="section__title">Latest from MWAMBE.</h2>
        </div>
        <div class="grid grid--3">
            <?php foreach ($latestNews as $a): ?>
                <article class="card card--article">
                    <?php if (!empty($a['media_filename'])): ?>
                        <img src="<?= e(asset('uploads/' . $a['media_filename'])) ?>" alt="<?= e($a['media_alt'] ?? '') ?>" class="card__image" loading="lazy">
                    <?php endif; ?>
                    <p class="card__meta">
                        <?php if (!empty($a['category_name'])): ?><?= e($a['category_name']) ?> · <?php endif; ?>
                        <time datetime="<?= e($a['published_at']) ?>"><?= e(date('d M Y', strtotime($a['published_at']))) ?></time>
                    </p>
                    <h3 class="card__title"><a href="/news/<?= e($a['slug']) ?>"><?= e($a['title']) ?></a></h3>
                    <p><?= e($a['excerpt']) ?></p>
                </article>
            <?php endforeach; ?>
        </div>
        <p class="section__actions"><a class="btn btn--primary" href="/news">All news</a></p>
    </div>
</section>
<?php endif; ?>

<!-- ================= PARTNERSHIP CTA ================= -->
<section class="section section--dark" data-reveal>
    <div class="container section--center">
        <p class="eyebrow eyebrow--gold">Partner With Us</p>
        <h2 class="section__title">The strongest partnerships are not simply transactions.</h2>
        <p class="section__lede">
            We welcome partnerships across programme co-design, funding, technical assistance,
            mentorship, training, research, sponsorship, equipment, market linkages and employment pathways.
        </p>
        <p class="section__actions">
            <a class="btn btn--primary" href="/partner-with-us">Start a Conversation</a>
            <a class="btn btn--ghost-light" href="/contact">Contact MWAMBE</a>
        </p>
    </div>
</section>