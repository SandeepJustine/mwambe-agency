<?php
$sectorSectionData = !empty($pageIntro['data'])
    ? json_decode((string) $pageIntro['data'], true)
    : [];
$sectorSectionData = is_array($sectorSectionData) ? $sectorSectionData : [];
$sectorCtaLabel = $sectorSectionData['cta_label'] ?? 'Explore a partnership';
$sectorCtaUrl = $sectorSectionData['cta_url'] ?? '/contact';
$validSectorCtaUrl = preg_match('#^(?:/(?!/)|https?://)#i', (string) $sectorCtaUrl) === 1;
?>
<section class="section section--alt sectors-section" aria-label="Sector focus areas" data-reveal>
    <div class="container">
        <?php if (empty($sectors)): ?>
            <div class="callout callout--info">
                <p>Sector profiles are being prepared. Please <a href="/contact">contact us</a> to discuss our work.</p>
            </div>
        <?php else: ?>
            <div class="sectors-grid">
                <?php foreach ($sectors as $sector): ?>
                    <?php
                    $sectorSlug = strtolower((string) $sector['slug']);
                    $sectorIcon = str_contains($sectorSlug, 'agriculture')
                        ? 'icon-agriculture'
                        : (str_contains($sectorSlug, 'education') ? 'icon-youth' : 'icon-community');
                    $imageFilename = $sector['media_filename'] ?? null;
                    $imageAlt = $sector['media_alt'] ?: $sector['name'];
                    ?>
                    <article class="sector-card<?= !empty($sector['is_featured']) ? ' sector-card--featured' : '' ?>">
                        <?php if ($imageFilename !== null): ?>
                            <img
                                class="sector-card__image"
                                src="<?= e(asset('uploads/' . $imageFilename)) ?>"
                                alt="<?= e($imageAlt) ?>"
                                loading="lazy"
                                decoding="async">
                        <?php else: ?>
                            <div class="sector-card__image sector-card__image--empty" aria-hidden="true">
                                <svg><use href="#<?= e($sectorIcon) ?>"></use></svg>
                            </div>
                        <?php endif; ?>
                        <div class="sector-card__content">
                            <p class="eyebrow">Sector <?= e(str_pad((string) $sector['position'], 2, '0', STR_PAD_LEFT)) ?></p>
                            <h3 class="sector-card__title"><?= e($sector['name']) ?></h3>
                            <?php if (!empty($sector['summary'])): ?>
                                <p><?= e($sector['summary']) ?></p>
                            <?php endif; ?>
                            <?php if (!empty($sector['body'])): ?>
                                <div class="sector-card__body"><?= nl2br(e($sector['body'])) ?></div>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?php if ($pageIntro !== null && $validSectorCtaUrl && $sectorCtaLabel !== ''): ?>
            <p class="section__actions"><a class="btn btn--primary" href="<?= e($sectorCtaUrl) ?>"><?= e($sectorCtaLabel) ?></a></p>
        <?php endif; ?>
    </div>
</section>
