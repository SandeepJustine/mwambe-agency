<section class="page-hero page-hero--minimal">
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Activities</p>
        <h1 class="page-hero__headline">Events &amp; Activities</h1>
        <p class="page-hero__lede">
            Workshops, sessions and community activities from MWAMBE Agency.
        </p>
    </div>
</section>

<?php if (!empty($upcoming)): ?>
<section class="section">
    <div class="container">
        <h2 class="section__title">Upcoming</h2>
        <ul class="event-list">
            <?php foreach ($upcoming as $ev): ?>
                <li class="event-list__item">
                    <time datetime="<?= e($ev['starts_at']) ?>" class="event-list__date">
                        <span class="event-list__day"><?= e(date('d', strtotime($ev['starts_at']))) ?></span>
                        <span class="event-list__month"><?= e(date('M Y', strtotime($ev['starts_at']))) ?></span>
                    </time>
                    <div class="event-list__body">
                        <h3><a href="/events/<?= e($ev['slug']) ?>"><?= e($ev['title']) ?></a></h3>
                        <?php if (!empty($ev['location'])): ?>
                            <p class="event-list__location"><?= e($ev['location']) ?></p>
                        <?php endif; ?>
                        <?php if (!empty($ev['summary'])): ?>
                            <p><?= e($ev['summary']) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php if (!empty($past)): ?>
<section class="section section--alt">
    <div class="container">
        <h2 class="section__title">Past activities</h2>
        <ul class="event-list">
            <?php foreach ($past as $ev): ?>
                <li class="event-list__item event-list__item--past">
                    <time datetime="<?= e($ev['starts_at']) ?>" class="event-list__date">
                        <span class="event-list__day"><?= e(date('d', strtotime($ev['starts_at']))) ?></span>
                        <span class="event-list__month"><?= e(date('M Y', strtotime($ev['starts_at']))) ?></span>
                    </time>
                    <div class="event-list__body">
                        <h3><a href="/events/<?= e($ev['slug']) ?>"><?= e($ev['title']) ?></a></h3>
                        <?php if (!empty($ev['summary'])): ?>
                            <p><?= e($ev['summary']) ?></p>
                        <?php endif; ?>
                    </div>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>
</section>
<?php endif; ?>

<?php if (empty($upcoming) && empty($past)): ?>
<section class="section">
    <div class="container container--narrow">
        <div class="callout callout--info">
            <h2 class="callout__title">No events scheduled.</h2>
            <p>Check back soon, or <a href="/contact">contact us</a> for information about upcoming activities.</p>
        </div>
    </div>
</section>
<?php endif; ?>