<section class="section section--alt team-section" aria-labelledby="team-title" data-reveal>
    <div class="container">
        <header class="section__header">
            <p class="eyebrow">The people behind the work</p>
            <h2 class="section__title" id="team-title">Our team.</h2>
            <p class="section__lede">Meet the people connecting skills, enterprise and opportunity across Malawi.</p>
        </header>

        <?php if (empty($teamMembers)): ?>
            <div class="callout callout--info">
                <p>Our team profiles will be added here soon.</p>
            </div>
        <?php else: ?>
            <div class="team-grid">
                <?php foreach ($teamMembers as $member): ?>
                    <article class="team-card">
                        <?php if (!empty($member['media_filename'])): ?>
                            <img
                                class="team-card__photo"
                                src="<?= e(asset('uploads/' . $member['media_filename'])) ?>"
                                alt="<?= e($member['media_alt'] ?: $member['name']) ?>"
                                loading="lazy"
                                decoding="async">
                        <?php else: ?>
                            <div class="team-card__photo team-card__photo--empty" aria-hidden="true">
                                <svg><use href="#icon-youth"></use></svg>
                            </div>
                        <?php endif; ?>
                        <div class="team-card__content">
                            <h3 class="team-card__name"><?= e($member['name']) ?></h3>
                            <p class="team-card__role"><?= e($member['position_title']) ?></p>
                            <?php if (!empty($member['bio'])): ?>
                                <p class="team-card__bio"><?= nl2br(e($member['bio'])) ?></p>
                            <?php endif; ?>
                        </div>
                    </article>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</section>
