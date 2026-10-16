<?php
/** @var array $page */
/** @var array $sections */

$heroImage = !empty($page['hero_filename'])
    ? asset('uploads/' . $page['hero_filename'])
    : null;
$heroAlt = (string) ($page['hero_alt'] ?? '');
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
        <section class="section section--alt" data-reveal>
            <div class="container">
                <?php if (!empty($s['heading'])): ?>
                    <div class="section__header">
                        <?php if (!empty($s['eyebrow'])): ?><p class="eyebrow"><?= e($s['eyebrow']) ?></p><?php endif; ?>
                        <h2 class="section__title"><?= e($s['heading']) ?></h2>
                    </div>
                <?php endif; ?>
                <div class="grid grid--3">
                    <?php foreach ($data['cards'] as $c): ?>
                        <article class="card">
                            <h3 class="card__title"><?= e($c['title']) ?></h3>
                            <p><?= e($c['body']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

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
                        <article class="feature">
                            <span class="feature__mark" aria-hidden="true"></span>
                            <h3 class="feature__title"><?= e($f['title']) ?></h3>
                            <p><?= e($f['body']) ?></p>
                        </article>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>

    <?php elseif ($type === 'cta_band'): ?>
        <section class="section section--dark" data-reveal>
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