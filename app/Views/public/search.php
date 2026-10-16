<section class="page-hero page-hero--minimal">
    <div class="container page-hero__inner">
        <p class="page-hero__eyebrow">Search</p>
        <h1 class="page-hero__headline">Find what you're looking for.</h1>
    </div>
</section>

<section class="section">
    <div class="container container--narrow">
        <form method="get" action="/search" class="search-form" role="search">
            <label for="q" class="visually-hidden">Search term</label>
            <input type="search" id="q" name="q" value="<?= e($query) ?>"
                   class="search-form__input" placeholder="Search pages, programmes and news..."
                   aria-label="Search term" autofocus>
            <button type="submit" class="btn btn--primary">Search</button>
        </form>

        <?php if ($query === ''): ?>
            <p class="search-hint">Enter at least two characters to search.</p>
        <?php elseif ($total === 0): ?>
            <div class="callout callout--info">
                <h2 class="callout__title">No results for "<?= e($query) ?>"</h2>
                <p>Try a different term, or <a href="/contact">contact us</a> directly.</p>
            </div>
        <?php else: ?>
            <p class="search-count"><?= (int) $total ?> result<?= $total === 1 ? '' : 's' ?> for "<?= e($query) ?>"</p>

            <?php foreach ($results as $group => $items): ?>
                <?php if (empty($items)) continue; ?>
                <section class="search-group">
                    <h2 class="search-group__title"><?= e(ucfirst($group)) ?></h2>
                    <ul class="search-results">
                        <?php foreach ($items as $r): ?>
                            <li class="search-results__item">
                                <h3><a href="<?= e($r['url']) ?>"><?= e($r['title']) ?></a></h3>
                                <?php if (!empty($r['excerpt'])): ?>
                                    <p><?= e(mb_substr((string) $r['excerpt'], 0, 200)) ?><?= mb_strlen((string) $r['excerpt']) > 200 ? '…' : '' ?></p>
                                <?php endif; ?>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</section>