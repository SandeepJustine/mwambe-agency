<section class="page-hero page-hero--editorial">
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Evidence</p>
        <h1 class="page-hero__headline">Evidence over activity.</h1>
        <p class="page-hero__lede">
            MWAMBE is committed to moving beyond activity-based reporting toward stronger evidence of results.
            Only verified impact figures are shown here.
        </p>
    </div>
</section>

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
    <section class="section" data-reveal>
        <div class="container">
            <div class="grid grid--3">
                <?php foreach ($indicators as $ind): ?>
                    <article class="stat-block stat-block--dark">
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