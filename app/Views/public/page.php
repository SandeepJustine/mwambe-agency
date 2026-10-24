<?php
/** @var array $page */
/** @var array $sections */

$heroImage = !empty($page['hero_filename'])
    ? asset('uploads/' . $page['hero_filename'])
    : null;
$heroAlt = (string) ($page['hero_alt'] ?? '');
$introImages = [
    'about' => ['filename' => '2026/10/2369d12bad240e561d82423f.jpg', 'alt' => 'Young people connecting and building opportunity together'],
    'our-work' => ['filename' => '2026/10/b91a77336946cdc341d7eb4e.jpg', 'alt' => 'Community members working together on a local project'],
    'impact' => ['filename' => '2026/10/684d51c258f1b3933e60cca6.jpg', 'alt' => 'Community members joining hands'],
    'sectors' => ['filename' => '2026/10/f208282a80c6f77505ec6b46.jpg', 'alt' => 'Young people taking part in a community activity'],
];
$introFallback = $introImages[$page['slug']] ?? null;
$introImage = !empty($page['hero_filename'])
    ? asset('uploads/' . $page['hero_filename'])
    : ($introFallback !== null ? asset('uploads/' . $introFallback['filename']) : null);
$introAlt = $heroAlt !== ''
    ? $heroAlt
    : (!empty($page['hero_filename'])
        ? ($pageIntro['heading'] ?? $page['title'])
        : ($introFallback['alt'] ?? ''));
?>

<!-- HERO -->
<section class="page-hero page-hero--<?= e($page['hero_variant']) ?>">
    <?php if ($heroImage): ?>
        <div class="page-hero__bg" aria-hidden="true">
            <img src="<?= e($heroImage) ?>" alt="<?= e($heroAlt) ?>">
        </div>
    <?php endif; ?>

    <div class="page-hero__geo" aria-hidden="true">
        <span class="page-hero__arc"></span>
    </div>

    <div class="container page-hero__inner">
        <?php if (!empty($page['eyebrow'])): ?>
            <p class="page-hero__eyebrow"><?= e($page['eyebrow']) ?></p>
        <?php endif; ?>
        <h1 class="page-hero__headline"><?= e($page['hero_headline'] ?: $page['title']) ?></h1>
        <?php if (!empty($page['hero_lede'])): ?>
            <p class="page-hero__lede"><?= e($page['hero_lede']) ?></p>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($pageIntro)): ?>
    <?php
    $introHeadingId = 'page-intro-heading';
    $introHasText = !empty($pageIntro['eyebrow']) || !empty($pageIntro['heading']) || !empty($pageIntro['body']);
    ?>
    <?php if ($introHasText && $introImage !== null): ?>
        <section class="section cms-intro" data-reveal
                 <?= !empty($pageIntro['heading'])
                     ? 'aria-labelledby="' . $introHeadingId . '"'
                     : (!empty($pageIntro['eyebrow']) ? 'aria-labelledby="page-intro-label"' : 'aria-label="Page introduction"') ?>>
            <div class="container cms-intro__grid">
                <div class="cms-intro__copy">
                    <?php if (!empty($pageIntro['eyebrow'])): ?>
                        <p class="eyebrow" <?= empty($pageIntro['heading']) ? 'id="page-intro-label"' : '' ?>><?= e($pageIntro['eyebrow']) ?></p>
                    <?php endif; ?>
                    <?php if (!empty($pageIntro['heading'])): ?>
                        <h2 class="section__title" id="<?= $introHeadingId ?>"><?= e($pageIntro['heading']) ?></h2>
                    <?php endif; ?>
                    <?php if (!empty($pageIntro['body'])): ?>
                        <div class="prose cms-intro__body"><?= nl2br(e($pageIntro['body'])) ?></div>
                    <?php endif; ?>
                </div>
                <figure class="cms-intro__visual">
                    <div class="cms-intro__image-wrap">
                        <img
                            class="cms-intro__image"
                            src="<?= e($introImage) ?>"
                            alt="<?= e($introAlt) ?>"
                            width="1000"
                            height="667"
                            loading="lazy"
                            decoding="async">
                    </div>
                    <figcaption class="cms-intro__caption">
                        <svg aria-hidden="true"><use href="#icon-pin"></use></svg>
                        Rooted in Malawi. Building opportunity together.
                    </figcaption>
                </figure>
            </div>
        </section>
    <?php elseif ($introHasText): ?>
        <section class="section" data-reveal>
            <div class="container container--narrow">
                <?php if (!empty($pageIntro['eyebrow'])): ?><p class="eyebrow"><?= e($pageIntro['eyebrow']) ?></p><?php endif; ?>
                <?php if (!empty($pageIntro['heading'])): ?><h2 class="section__title"><?= e($pageIntro['heading']) ?></h2><?php endif; ?>
                <?php if (!empty($pageIntro['body'])): ?><div class="prose"><?= nl2br(e($pageIntro['body'])) ?></div><?php endif; ?>
            </div>
        </section>
    <?php endif; ?>
<?php endif; ?>

<?php if (($page['slug'] ?? '') === 'our-work'): ?>
    <section class="section section--alt work-services" data-reveal aria-labelledby="work-services-title">
        <div class="container">
            <div class="section__header">
                <p class="eyebrow">What we offer</p>
                <h2 class="section__title" id="work-services-title">Our services</h2>
                <p class="section__lede">Practical support shaped around the people, enterprises and communities we work with.</p>
            </div>
            <?php if (empty($services)): ?>
                <div class="callout callout--info">
                    <p>Our services are being updated. Please <a href="/contact">contact us</a> to discuss how we can work together.</p>
                </div>
            <?php else: ?>
                <div class="work-services__grid">
                    <?php foreach ($services as $service): ?>
                        <?php
                        $serviceKey = strtolower(trim((string) ($service['icon_key'] ?? '')));
                        $serviceCategory = strtolower(trim((string) ($service['category'] ?? '')));
                        $serviceName = strtolower((string) ($service['name'] ?? ''));
                        $serviceIconKey = $serviceKey . ' ' . $serviceCategory . ' ' . $serviceName;
                        $serviceIcon = str_contains($serviceIconKey, 'agri')
                            ? 'icon-agriculture'
                            : (str_contains($serviceIconKey, 'enterprise') || str_contains($serviceIconKey, 'business')
                                ? 'icon-enterprise'
                                : (str_contains($serviceIconKey, 'youth') || str_contains($serviceIconKey, 'education')
                                    ? 'icon-youth'
                                    : 'icon-community'));
                        ?>
                        <article class="work-service">
                            <?php if (!empty($service['media_filename'])): ?>
                                <img
                                    class="work-service__image"
                                    src="<?= e(asset('uploads/' . $service['media_filename'])) ?>"
                                    alt="<?= e($service['media_alt'] ?: $service['name']) ?>"
                                    loading="lazy"
                                    decoding="async">
                            <?php else: ?>
                                <div class="work-service__image work-service__image--empty" aria-hidden="true">
                                    <svg><use href="#<?= e($serviceIcon) ?>"></use></svg>
                                </div>
                            <?php endif; ?>
                            <div class="work-service__content">
                                <?php if (!empty($service['category'])): ?>
                                    <p class="work-service__category"><?= e($service['category']) ?></p>
                                <?php endif; ?>
                                <h3><?= e($service['name']) ?></h3>
                                <?php if (!empty($service['summary'])): ?>
                                    <p><?= e($service['summary']) ?></p>
                                <?php endif; ?>
                                <?php if (!empty($service['body'])): ?>
                                    <div class="work-service__body"><?= nl2br(e($service['body'])) ?></div>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </section>
<?php endif; ?>

<!-- SECTIONS -->
<?php foreach ($sections as $s): ?>
    <?php
    $data = $s['data'] ? json_decode((string) $s['data'], true) : [];
    $type = $s['section_type'];
    ?>
    <?php if ($type === 'rich_text'): ?>
        <section class="section" data-reveal>
            <div class="container container--narrow">
                <?php if (!empty($s['eyebrow'])): ?><p class="eyebrow"><?= e($s['eyebrow']) ?></p><?php endif; ?>
                <?php if (!empty($s['heading'])): ?><h2 class="section__title"><?= e($s['heading']) ?></h2><?php endif; ?>
                <?php if (!empty($s['body'])): ?>
                    <div class="prose"><?= nl2br(e($s['body'])) ?></div>
                <?php endif; ?>
            </div>
        </section>

    <?php elseif ($type === 'cards' && !empty($data['cards'])): ?>
        <?php
        $isAboutPurpose = ($page['slug'] ?? '') === 'about'
            && stripos((string) ($s['heading'] ?? ''), 'vision') !== false;
        $purposeCards = $data['cards'];
        $approachCard = null;

        if ($isAboutPurpose) {
            $purposeCards = [];
            foreach ($data['cards'] as $card) {
                if (stripos((string) ($card['title'] ?? ''), 'approach') !== false) {
                    $approachCard = $card;
                } else {
                    $purposeCards[] = $card;
                }
            }
        }
        ?>
        <section class="section section--alt<?= $isAboutPurpose ? ' about-purpose' : '' ?>" data-reveal>
            <div class="container">
                <?php if (!empty($s['heading'])): ?>
                    <div class="section__header">
                        <?php if (!empty($s['eyebrow'])): ?><p class="eyebrow"><?= e($s['eyebrow']) ?></p><?php endif; ?>
                        <h2 class="section__title"><?= e($s['heading']) ?></h2>
                    </div>
                <?php endif; ?>
                <div class="grid <?= $isAboutPurpose ? 'grid--2 about-purpose__grid' : 'grid--3' ?>">
                    <?php foreach ($purposeCards as $c): ?>
                        <?php
                        $purposeTitle = strtolower((string) ($c['title'] ?? ''));
                        $purposeIcon = str_contains($purposeTitle, 'vision')
                            ? 'icon-eye'
                            : (str_contains($purposeTitle, 'mission') ? 'icon-target' : 'icon-community');
                        ?>
                        <article class="card<?= $isAboutPurpose ? ' about-purpose__card' : '' ?>">
                            <?php if ($isAboutPurpose): ?>
                                <span class="about-purpose__icon">
                                    <svg aria-hidden="true"><use href="#<?= e($purposeIcon) ?>"></use></svg>
                                </span>
                            <?php endif; ?>
                            <h3 class="card__title"><?= e($c['title']) ?></h3>
                            <p><?= e($c['body']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
        <?php if ($isAboutPurpose && $approachCard !== null): ?>
            <?php
            $approachBody = trim((string) ($approachCard['body'] ?? ''));
            $approachParts = preg_split('/\s*(?:→|->)\s*/u', $approachBody, -1, PREG_SPLIT_NO_EMPTY) ?: [];
            $approachDetail = '';

            if ($approachParts !== []) {
                $lastPart = array_pop($approachParts);
                if (preg_match('/^(.+?)[.!?]\s+(.+)$/u', $lastPart, $lastSentences) === 1) {
                    $approachParts[] = trim($lastSentences[1]);
                    $approachDetail = trim($lastSentences[2]);
                } else {
                    $approachParts[] = trim($lastPart, " \t\n\r\0\x0B.!?");
                }
            }
            $approachIcons = ['icon-search', 'icon-book-open', 'icon-link', 'icon-action', 'icon-chart'];
            ?>
            <section class="section about-approach" data-reveal aria-labelledby="about-approach-title">
                <div class="container">
                    <div class="section__header">
                        <p class="eyebrow">How we work</p>
                        <h2 class="section__title" id="about-approach-title"><?= e($approachCard['title']) ?></h2>
                    </div>
                    <?php if ($approachParts !== []): ?>
                        <ol class="about-approach__steps">
                            <?php foreach ($approachParts as $index => $step): ?>
                                <li class="about-approach__step">
                                    <span class="about-approach__marker">
                                        <span class="about-approach__number"><?= sprintf('%02d', $index + 1) ?></span>
                                        <svg aria-hidden="true">
                                            <use href="#<?= e($approachIcons[$index] ?? 'icon-community') ?>"></use>
                                        </svg>
                                    </span>
                                    <h3><?= e($step) ?></h3>
                                </li>
                            <?php endforeach; ?>
                        </ol>
                    <?php endif; ?>
                    <?php if ($approachDetail !== ''): ?>
                        <p class="about-approach__detail"><?= e($approachDetail) ?></p>
                    <?php endif; ?>
                </div>
            </section>
        <?php endif; ?>

    <?php elseif ($type === 'features' && !empty($data['items'])): ?>
        <section class="section" data-reveal>
            <div class="container">
                <?php if (!empty($s['heading'])): ?>
                    <div class="section__header">
                        <?php if (!empty($s['eyebrow'])): ?><p class="eyebrow"><?= e($s['eyebrow']) ?></p><?php endif; ?>
                        <h2 class="section__title"><?= e($s['heading']) ?></h2>
                    </div>
                <?php endif; ?>
                <div class="grid grid--2">
                    <?php foreach ($data['items'] as $f): ?>
                        <?php
                        $featureTitle = strtolower((string) ($f['title'] ?? ''));
                        $featureIcon = match (true) {
                            str_contains($featureTitle, 'equal') => 'icon-equality',
                            str_contains($featureTitle, 'opportun') => 'icon-target',
                            str_contains($featureTitle, 'integrity') => 'icon-integrity',
                            str_contains($featureTitle, 'collaborat') => 'icon-collaboration',
                            str_contains($featureTitle, 'learn') => 'icon-book-open',
                            str_contains($featureTitle, 'innovat') => 'icon-innovation',
                            str_contains($featureTitle, 'sustain') => 'icon-sustainability',
                            str_contains($featureTitle, 'accountab') => 'icon-chart',
                            str_contains($featureTitle, 'agri') => 'icon-agriculture',
                            str_contains($featureTitle, 'entrepreneur'), str_contains($featureTitle, 'business') => 'icon-enterprise',
                            str_contains($featureTitle, 'youth'), str_contains($featureTitle, 'education') => 'icon-youth',
                            default => 'icon-community',
                        };
                        ?>
                        <article class="feature">
                            <svg class="feature__icon" aria-hidden="true"><use href="#<?= e($featureIcon) ?>"></use></svg>
                            <h3 class="feature__title"><?= e($f['title']) ?></h3>
                            <p><?= e($f['body']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php elseif ($type === 'cta_band'): ?>
        <?php $isWorkCta = ($page['slug'] ?? '') === 'our-work'; ?>
        <section class="section section--dark<?= $isWorkCta ? ' work-cta' : '' ?>" data-reveal>
            <?php if ($isWorkCta && $introImage !== null): ?>
                <div class="work-cta__background" aria-hidden="true">
                    <img src="<?= e($introImage) ?>" alt="" loading="lazy" decoding="async">
                </div>
            <?php endif; ?>
            <div class="container section--center">
                <h2 class="section__title"><?= e($s['heading'] ?? '') ?></h2>
                <?php if (!empty($s['body'])): ?><p class="section__lede"><?= e($s['body']) ?></p><?php endif; ?>
                <?php if (!empty($data['buttons'])): ?>
                    <p class="section__actions">
                        <?php foreach ($data['buttons'] as $b): ?>
                            <a class="btn <?= !empty($b['primary']) ? 'btn--primary' : 'btn--ghost-light' ?>" href="<?= e($b['url']) ?>"><?= e($b['label']) ?></a>
                        <?php endforeach; ?>
                    </p>
                <?php endif; ?>
            </div>
        </section>

    <?php elseif ($type === 'quote' && !empty($s['body'])): ?>
        <section class="section" data-reveal>
            <div class="container container--narrow">
                <blockquote class="quote">
                    <p><?= e($s['body']) ?></p>
                    <?php if (!empty($s['heading'])): ?><footer>— <?= e($s['heading']) ?></footer><?php endif; ?>
                </blockquote>
            </div>
        </section>
    <?php endif; ?>
<?php endforeach; ?>

<!-- Extra page-specific content injected by dedicated controllers -->
<?php if (!empty($extraViewPath) && is_file($extraViewPath)): ?>
    <?php require $extraViewPath; ?>
<?php endif; ?>