<section class="page-hero page-hero--<?= !empty($programme['media_filename']) ? 'image_led' : 'split' ?>">
    <?php if (!empty($programme['media_filename'])): ?>
        <div class="page-hero__bg" aria-hidden="true">
            <img src="<?= e(asset('uploads/' . $programme['media_filename'])) ?>"
                 alt="<?= e($programme['media_alt'] ?? '') ?>">
        </div>
    <?php endif; ?>
    <div class="page-hero__geo" aria-hidden="true"><span class="page-hero__arc"></span></div>
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">
            <?= $programme['kind'] === 'project' ? 'Project' : 'Programme' ?>
            · <span class="status status--<?= e($programme['status']) ?>"><?= e(str_replace('_', ' ', $programme['status'])) ?></span>
        </p>
        <h1 class="page-hero__headline"><?= e($programme['title']) ?></h1>
        <?php if (!empty($programme['summary'])): ?>
            <p class="page-hero__lede"><?= e($programme['summary']) ?></p>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($programme['body'])): ?>
<section class="section">
    <div class="container container--narrow">
        <div class="prose"><?= nl2br(e($programme['body'])) ?></div>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($objectives)): ?>
<section class="section section--alt">
    <div class="container container--narrow">
        <h2 class="section__title">Objectives</h2>
        <ul class="checklist">
            <?php foreach ($objectives as $o): ?>
                <li><?= e($o) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($audience)): ?>
<section class="section">
    <div class="container container--narrow">
        <h2 class="section__title">Who it's for</h2>
        <ul class="checklist checklist--audience">
            <?php foreach ($audience as $a): ?>
                <li><?= e($a) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($activities)): ?>
<section class="section section--alt">
    <div class="container container--narrow">
        <h2 class="section__title">Activities</h2>
        <ul class="checklist">
            <?php foreach ($activities as $a): ?>
                <li><?= e($a) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($programme['timeline']) || !empty($programme['start_date'])): ?>
<section class="section">
    <div class="container container--narrow">
        <h2 class="section__title">Timeline</h2>
        <?php if (!empty($programme['timeline'])): ?>
            <p><?= e($programme['timeline']) ?></p>
        <?php endif; ?>
        <?php if (!empty($programme['start_date'])): ?>
            <p><strong>Start:</strong> <?= e(date('F Y', strtotime($programme['start_date']))) ?>
            <?php if (!empty($programme['end_date'])): ?>
                · <strong>End:</strong> <?= e(date('F Y', strtotime($programme['end_date']))) ?>
            <?php endif; ?>
            </p>
        <?php endif; ?>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($programme['outcomes'])): ?>
<section class="section section--alt">
    <div class="container container--narrow">
        <h2 class="section__title">Outcomes</h2>
        <div class="prose"><?= nl2br(e($programme['outcomes'])) ?></div>
    </div>
</section>
<?php endif; ?>

<section class="section section--dark">
    <div class="container section--center">
        <h2 class="section__title">Interested in this programme?</h2>
        <p class="section__lede">
            Partners, participants and institutions are welcome to contact MWAMBE for more information.
        </p>
        <p class="section__actions">
            <a class="btn btn--primary" href="/partner-with-us">Partner with us</a>
            <a class="btn btn--ghost-light" href="/contact">Contact MWAMBE</a>
        </p>
    </div>
</section>