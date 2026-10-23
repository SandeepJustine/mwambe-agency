<?php
/**
 * Admin dashboard — redesigned.
 *
 * Context passed in by DashboardController::index():
 *
 *   $stats           array{slides:int, programmes:int, enquiries30:int,
 *                          enquiriesDelta:?int, media:int, users:int}
 *   $needsAttention  array<int,array{label:string,url:string}>
 *   $enquiriesByWeek array<int,array{label:string,value:int}>   last 8 weeks
 *   $actionsByDay    array<int,array{label:string,value:int}>   last 14 days
 *   $contentMix      array{published:int,draft:int,archived:int,total:int,
 *                          published_pct:int,draft_pct:int,archived_pct:int}
 *   $recentContent   array<int,array{type,id,title,status,updated_at,url}>
 *   $recentEnquiries array<int,array{type,reference,sender,topic,status,created_at,url}>
 *   $upcomingEvents  array<int,array{id,title,starts_at,location}>
 *   $auth            Auth
 *   $csrf            Csrf
 */

// ---- Helpers ----

$formatDelta = static function (?int $delta): array {
    if ($delta === null) return ['', 'flat'];
    if ($delta > 0)  return ['+' . $delta . '%', 'up'];
    if ($delta < 0)  return [$delta . '%', 'down'];
    return ['0%', 'flat'];
};

$pillClass = static function (string $status): string {
    return 'pill pill--' . preg_replace('/[^a-z_]/', '', strtolower($status));
};

$statusLabel = static function (string $status): string {
    return ucfirst(str_replace('_', ' ', $status));
};

// ---- Build the line-chart geometry (server-side, no JS) ----

$linePoints = [];
$lineMaxValue = 1;
if (!empty($enquiriesByWeek)) {
    foreach ($enquiriesByWeek as $p) {
        $lineMaxValue = max($lineMaxValue, (int) $p['value']);
    }
}
$lineMaxValue = (int) ceil($lineMaxValue * 1.25); // headroom

$chartWidth  = 640;
$chartHeight = 240;
$chartPadL   = 40;
$chartPadR   = 16;
$chartPadT   = 20;
$chartPadB   = 34;
$plotW = $chartWidth - $chartPadL - $chartPadR;
$plotH = $chartHeight - $chartPadT - $chartPadB;

$stepX = count($enquiriesByWeek) > 1
    ? $plotW / (count($enquiriesByWeek) - 1)
    : $plotW;

$points = [];
foreach ($enquiriesByWeek as $i => $p) {
    $x = $chartPadL + ($i * $stepX);
    $y = $chartPadT + $plotH - ((int) $p['value'] / $lineMaxValue) * $plotH;
    $points[] = ['x' => round($x, 1), 'y' => round($y, 1), 'v' => (int) $p['value'], 'label' => $p['label']];
}
$polyline = implode(' ', array_map(fn($p) => $p['x'] . ',' . $p['y'], $points));
$areaPath = '';
if (!empty($points)) {
    $areaPath = 'M ' . $points[0]['x'] . ' ' . ($chartPadT + $plotH);
    foreach ($points as $p) {
        $areaPath .= ' L ' . $p['x'] . ' ' . $p['y'];
    }
    $areaPath .= ' L ' . $points[count($points) - 1]['x'] . ' ' . ($chartPadT + $plotH) . ' Z';
}

// ---- Bar-chart geometry ----

$barMax = 1;
foreach ($actionsByDay as $d) {
    $barMax = max($barMax, (int) $d['value']);
}
$barMax = (int) ceil($barMax * 1.15);
$barCount = max(1, count($actionsByDay));
$barGap = 8;
$barWidth = max(10, (int) ((560 - ($barGap * ($barCount - 1))) / $barCount));

// ---- Donut geometry ----

$donutRadius = 70;
$donutStroke = 26;
$donutCirc   = 2 * M_PI * $donutRadius;
$pubArc      = $contentMix['published_pct'] / 100 * $donutCirc;
$draftArc    = $contentMix['draft_pct']     / 100 * $donutCirc;
$archArc     = $contentMix['archived_pct']  / 100 * $donutCirc;
?>

<div class="dash">

    <!-- ============================================================
         Header
         ============================================================ -->
    <header class="dash__header">
        <div class="dash__title-block">
            <p class="dash__eyebrow">MWAMBE CMS</p>
            <h2 class="dash__title">Dashboard overview</h2>
            <p class="dash__lede">A clear view of your content, activity and next steps.</p>
        </div>
        <div class="dash__tools">
            <form class="dash__search" method="get" action="/admin/search" role="search">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/>
                </svg>
                <input type="search" name="q" placeholder="Search programmes, articles…" aria-label="Search">
            </form>
            <a href="/admin/system/audit" class="dash__iconbtn" aria-label="Audit log" title="Audit log">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 3"/></svg>
            </a>
            <a href="/" target="_blank" rel="noopener" class="dash__iconbtn" aria-label="View public site" title="View public site">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            </a>
            <a href="/admin/profile" class="dash__avatar" title="My profile">
                <?= e(mb_strtoupper(mb_substr((string) ($auth->user()['name'] ?? 'U'), 0, 1))) ?>
            </a>
        </div>
    </header>

    <!-- ============================================================
         Stat cards
         ============================================================ -->
    <?php [$deltaText, $deltaDir] = $formatDelta($stats['enquiriesDelta'] ?? null); ?>
    <section class="dash__stats" aria-label="Key numbers">

        <article class="statcard">
            <span class="statcard__icon statcard__icon--purple" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><path d="M9 9h6v6H9z"/></svg>
            </span>
            <div class="statcard__body">
                <p class="statcard__label">Active hero slides</p>
                <p class="statcard__value"><?= (int) $stats['slides'] ?> <span style="font-size:13px;color:var(--dash-muted);font-weight:500">/ 3</span></p>
            </div>
        </article>

        <article class="statcard">
            <span class="statcard__icon statcard__icon--blue" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 20V8l8-5 8 5v12"/><path d="M9 20v-6h6v6"/></svg>
            </span>
            <div class="statcard__body">
                <p class="statcard__label">Published programmes</p>
                <p class="statcard__value"><?= (int) $stats['programmes'] ?></p>
            </div>
        </article>

        <article class="statcard">
            <span class="statcard__icon statcard__icon--cyan" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 0 1-2 2H7l-4 4V5a2 2 0 0 1 2-2h14a2 2 0 0 1 2 2z"/></svg>
            </span>
            <div class="statcard__body">
                <p class="statcard__label">Enquiries (30 days)</p>
                <p class="statcard__value"><?= (int) $stats['enquiries30'] ?></p>
                <?php if ($deltaText !== ''): ?>
                    <p class="statcard__delta statcard__delta--<?= e($deltaDir) ?>"><?= e($deltaText) ?> vs prior 30 days</p>
                <?php endif; ?>
            </div>
        </article>

        <article class="statcard">
            <span class="statcard__icon statcard__icon--orange" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="18" height="18" rx="3"/><circle cx="9" cy="9" r="2"/><path d="M21 15l-5-5L5 21"/></svg>
            </span>
            <div class="statcard__body">
                <p class="statcard__label">Media files</p>
                <p class="statcard__value"><?= (int) $stats['media'] ?></p>
            </div>
        </article>

        <article class="statcard">
            <span class="statcard__icon statcard__icon--green" aria-hidden="true">
                <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
            </span>
            <div class="statcard__body">
                <p class="statcard__label">Active users</p>
                <p class="statcard__value"><?= (int) $stats['users'] ?></p>
            </div>
        </article>
    </section>

    <!-- ============================================================
         Row 1 — Enquiries line chart + Needs attention
         ============================================================ -->
    <section class="dash__row">

        <div class="dcard">
            <header class="dcard__head">
                <h2 class="dcard__title">Enquiries over time</h2>
                <span class="dcard__link" style="cursor:default">Last <?= count($enquiriesByWeek) ?> weeks</span>
            </header>

            <?php if ($lineMaxValue <= 1 && empty(array_filter(array_column($enquiriesByWeek, 'value')))): ?>
                <p class="admin__empty" style="margin:0">No enquiries recorded in the last <?= count($enquiriesByWeek) ?> weeks.</p>
            <?php else: ?>
                <svg class="chart-line" viewBox="0 0 <?= $chartWidth ?> <?= $chartHeight ?>" role="img"
                     aria-label="Enquiries over the last <?= count($enquiriesByWeek) ?> weeks">
                    <defs>
                        <linearGradient id="dashAreaGradient" x1="0" y1="0" x2="0" y2="1">
                            <stop offset="0%"   stop-color="#C29A2C" stop-opacity="0.32"/>
                            <stop offset="100%" stop-color="#C29A2C" stop-opacity="0"/>
                        </linearGradient>
                    </defs>

                    <!-- Horizontal grid lines -->
                    <?php for ($g = 0; $g <= 4; $g++): ?>
                        <?php $gy = $chartPadT + ($plotH / 4) * $g; ?>
                        <line class="chart-line__grid"
                              x1="<?= $chartPadL ?>" x2="<?= $chartWidth - $chartPadR ?>"
                              y1="<?= round($gy, 1) ?>" y2="<?= round($gy, 1) ?>"/>
                    <?php endfor; ?>

                    <!-- Y-axis labels -->
                    <?php for ($g = 0; $g <= 4; $g++): ?>
                        <?php
                        $gy = $chartPadT + ($plotH / 4) * $g;
                        $gv = (int) round($lineMaxValue * (1 - $g / 4));
                        ?>
                        <text class="chart-line__label"
                              x="<?= $chartPadL - 8 ?>" y="<?= round($gy + 4, 1) ?>"
                              text-anchor="end"><?= $gv ?></text>
                    <?php endfor; ?>

                    <!-- Area -->
                    <path class="chart-line__area" d="<?= $areaPath ?>"/>

                    <!-- Line -->
                    <polyline class="chart-line__line" points="<?= $polyline ?>"/>

                    <!-- Dots -->
                    <?php foreach ($points as $p): ?>
                        <circle class="chart-line__dot" cx="<?= $p['x'] ?>" cy="<?= $p['y'] ?>" r="4"/>
                    <?php endforeach; ?>

                    <!-- X-axis labels -->
                    <?php foreach ($points as $i => $p): ?>
                        <?php if ($i === 0 || $i === count($points) - 1 || $i % 2 === 0): ?>
                            <text class="chart-line__label"
                                  x="<?= $p['x'] ?>" y="<?= $chartHeight - 12 ?>"
                                  text-anchor="middle"><?= e($p['label']) ?></text>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </svg>
            <?php endif; ?>
        </div>

        <div class="dcard">
            <header class="dcard__head">
                <h2 class="dcard__title">Needs attention</h2>
                <?php if (empty($needsAttention)): ?>
                    <span class="dcard__link" style="cursor:default;color:var(--dash-green)">All clear</span>
                <?php endif; ?>
            </header>

            <?php if (empty($needsAttention)): ?>
                <p class="admin__empty" style="margin:0">Nothing requires action right now.</p>
            <?php else: ?>
                <ul class="tasklist">
                    <?php foreach ($needsAttention as $item): ?>
                        <li>
                            <a class="tasklist__item tasklist__item--active" href="<?= e($item['url']) ?>">
                                <span class="tasklist__checkbox" aria-hidden="true">
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                                </span>
                                <span class="tasklist__text"><?= e($item['label']) ?></span>
                                <span class="tasklist__arrow" aria-hidden="true">→</span>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============================================================
         Row 2 — Audit activity bar chart + Content mix donut
         ============================================================ -->
    <section class="dash__row">

        <div class="dcard">
            <header class="dcard__head">
                <h2 class="dcard__title">Administrative activity</h2>
                <span class="dcard__link" style="cursor:default">Last <?= count($actionsByDay) ?> days</span>
            </header>

            <?php if ($barMax <= 1 && empty(array_filter(array_column($actionsByDay, 'value')))): ?>
                <p class="admin__empty" style="margin:0">No administrative actions recorded yet.</p>
            <?php else: ?>
                <div class="chart-bar" role="img" aria-label="Administrative actions per day">
                    <?php foreach ($actionsByDay as $d): ?>
                        <?php
                        $h = $barMax > 0 ? (int) (((int) $d['value'] / $barMax) * 160) : 0;
                        $h = max(4, $h);
                        ?>
                        <div class="chart-bar__col">
                            <div class="chart-bar__bar"
                                 style="height:<?= $h ?>px"
                                 title="<?= (int) $d['value'] ?> on <?= e($d['label']) ?>"></div>
                            <span class="chart-bar__label"><?= e(date('j', strtotime($d['label']))) ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="dcard">
            <header class="dcard__head">
                <h2 class="dcard__title">Content by status</h2>
            </header>

            <?php if ($contentMix['total'] === 0): ?>
                <p class="admin__empty" style="margin:0">No content yet.</p>
            <?php else: ?>
                <div class="donut" role="img" aria-label="Content mix by workflow status">
                    <svg viewBox="0 0 200 200">
                        <!-- Track circle (background ring) -->
                        <circle class="donut__track" cx="100" cy="100" r="<?= $donutRadius ?>"
                                fill="none" stroke-width="<?= $donutStroke ?>"/>

                        <!-- Published arc (starts at top, clockwise) -->
                        <?php if ($contentMix['published_pct'] > 0): ?>
                            <circle class="donut__segment--published" cx="100" cy="100" r="<?= $donutRadius ?>"
                                    fill="none"
                                    stroke-width="<?= $donutStroke ?>"
                                    stroke-dasharray="<?= round($pubArc, 2) ?> <?= round($donutCirc - $pubArc, 2) ?>"
                                    stroke-dashoffset="0"
                                    transform="rotate(-90 100 100)"/>
                        <?php endif; ?>

                        <!-- Draft arc (offset after published) -->
                        <?php if ($contentMix['draft_pct'] > 0): ?>
                            <circle class="donut__segment--draft" cx="100" cy="100" r="<?= $donutRadius ?>"
                                    fill="none"
                                    stroke-width="<?= $donutStroke ?>"
                                    stroke-dasharray="<?= round($draftArc, 2) ?> <?= round($donutCirc - $draftArc, 2) ?>"
                                    stroke-dashoffset="<?= -round($pubArc, 2) ?>"
                                    transform="rotate(-90 100 100)"/>
                        <?php endif; ?>

                        <!-- Archived arc -->
                        <?php if ($contentMix['archived_pct'] > 0): ?>
                            <circle class="donut__segment--archived" cx="100" cy="100" r="<?= $donutRadius ?>"
                                    fill="none"
                                    stroke-width="<?= $donutStroke ?>"
                                    stroke-dasharray="<?= round($archArc, 2) ?> <?= round($donutCirc - $archArc, 2) ?>"
                                    stroke-dashoffset="<?= -round($pubArc + $draftArc, 2) ?>"
                                    transform="rotate(-90 100 100)"/>
                        <?php endif; ?>
                    </svg>
                    <div class="donut__center">
                        <strong><?= (int) $contentMix['total'] ?></strong>
                        <span>items</span>
                    </div>
                </div>

                <div class="donut__legend">
                    <div class="donut__legend-item">
                        <span class="donut__swatch donut__swatch--published"></span>
                        <span class="donut__legend-label">Published</span>
                        <span class="donut__legend-value"><?= (int) $contentMix['published'] ?></span>
                    </div>
                    <div class="donut__legend-item">
                        <span class="donut__swatch donut__swatch--draft"></span>
                        <span class="donut__legend-label">Draft</span>
                        <span class="donut__legend-value"><?= (int) $contentMix['draft'] ?></span>
                    </div>
                    <div class="donut__legend-item">
                        <span class="donut__swatch donut__swatch--archived"></span>
                        <span class="donut__legend-label">Archived</span>
                        <span class="donut__legend-value"><?= (int) $contentMix['archived'] ?></span>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============================================================
         Row 3 — Recent content + Recent enquiries
         ============================================================ -->
    <section class="dash__row">

        <div class="dcard">
            <header class="dcard__head">
                <h2 class="dcard__title">Recent content</h2>
                <a class="dcard__link" href="/admin/programmes">Manage →</a>
            </header>

            <?php if (empty($recentContent)): ?>
                <p class="admin__empty" style="margin:0">Nothing published yet.</p>
            <?php else: ?>
                <div class="dtable-wrap" role="region" aria-label="Recent content" tabindex="0">
                    <table class="dtable">
                        <thead>
                            <tr>
                                <th scope="col">Title</th>
                                <th scope="col">Type</th>
                                <th scope="col">Status</th>
                                <th scope="col">Updated</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentContent as $c): ?>
                                <tr>
                                    <td><a href="<?= e($c['url']) ?>"><?= e($c['title']) ?></a></td>
                                    <td style="text-transform:capitalize;color:var(--dash-muted)"><?= e($c['type']) ?></td>
                                    <td>
                                        <span class="<?= e($pillClass($c['status'])) ?>">
                                            <?= e($statusLabel($c['status'])) ?>
                                        </span>
                                    </td>
                                    <td style="color:var(--dash-muted)">
                                        <?= e(date('d M Y', strtotime($c['updated_at']))) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>

        <div class="dcard">
            <header class="dcard__head">
                <h2 class="dcard__title">Recent enquiries</h2>
                <a class="dcard__link" href="/admin/enquiries/contact">All →</a>
            </header>

            <?php if (empty($recentEnquiries)): ?>
                <p class="admin__empty" style="margin:0">No enquiries yet.</p>
            <?php else: ?>
                <div class="dtable-wrap" role="region" aria-label="Recent enquiries" tabindex="0">
                    <table class="dtable">
                        <thead>
                            <tr>
                                <th scope="col">From</th>
                                <th scope="col">Topic</th>
                                <th scope="col">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentEnquiries as $e): ?>
                                <tr>
                                    <td>
                                        <a href="<?= e($e['url']) ?>"><?= e($e['sender']) ?></a>
                                        <br>
                                        <small style="color:var(--dash-muted)"><code><?= e($e['reference']) ?></code></small>
                                    </td>
                                    <td style="color:var(--dash-muted)">
                                        <?= e(mb_strimwidth((string) ($e['topic'] ?: '—'), 0, 30, '…')) ?>
                                    </td>
                                    <td>
                                        <span class="<?= e($pillClass($e['status'])) ?>">
                                            <?= e($statusLabel($e['status'])) ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- ============================================================
         Row 4 — Upcoming events + Quick links
         ============================================================ -->
    <section class="dash__row">

        <div class="dcard">
            <header class="dcard__head">
                <h2 class="dcard__title">Upcoming events</h2>
                <a class="dcard__link" href="/admin/events">All events →</a>
            </header>

            <?php if (empty($upcomingEvents)): ?>
                <p class="admin__empty" style="margin:0">
                    No upcoming events. <a href="/admin/events/new">Schedule one</a>.
                </p>
            <?php else: ?>
                <div class="minievents">
                    <?php foreach ($upcomingEvents as $ev): ?>
                        <a class="minievent" href="/admin/events/<?= (int) $ev['id'] ?>/edit">
                            <span class="minievent__date">
                                <span class="minievent__day"><?= e(date('d', strtotime($ev['starts_at']))) ?></span>
                                <span class="minievent__month"><?= e(date('M', strtotime($ev['starts_at']))) ?></span>
                            </span>
                            <span>
                                <p class="minievent__title"><?= e($ev['title']) ?></p>
                                <?php if (!empty($ev['location'])): ?>
                                    <p class="minievent__meta"><?= e($ev['location']) ?></p>
                                <?php endif; ?>
                            </span>
                        </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <div class="dcard">
            <header class="dcard__head">
                <h2 class="dcard__title">Quick links</h2>
            </header>
            <div class="tasklist">
                <a class="tasklist__item" href="/admin/homepage/slides">
                    <span class="tasklist__text">Edit homepage hero slides</span>
                    <span class="tasklist__arrow" aria-hidden="true">→</span>
                </a>
                <a class="tasklist__item" href="/admin/media">
                    <span class="tasklist__text">Upload an image</span>
                    <span class="tasklist__arrow" aria-hidden="true">→</span>
                </a>
                <a class="tasklist__item" href="/admin/news/new">
                    <span class="tasklist__text">Publish a news article</span>
                    <span class="tasklist__arrow" aria-hidden="true">→</span>
                </a>
                <a class="tasklist__item" href="/admin/impact">
                    <span class="tasklist__text">Review impact indicators</span>
                    <span class="tasklist__arrow" aria-hidden="true">→</span>
                </a>
            </div>
        </div>
    </section>

    <!-- ============================================================
         Bottom — two promo cards matching the reference
         ============================================================ -->
    <section class="quicklinks">
        <a class="qlink qlink--purple" href="/contact">
            <span class="qlink__body">
                <span class="qlink__eyebrow">Public site</span>
                <p class="qlink__title">Contact form preview</p>
                <p class="qlink__cta">See how visitors reach MWAMBE →</p>
            </span>
            <span class="qlink__icon" aria-hidden="true">✉</span>
        </a>
        <a class="qlink qlink--dark" href="/partner-with-us">
            <span class="qlink__body">
                <span class="qlink__eyebrow">Public site</span>
                <p class="qlink__title">Partnership enquiry</p>
                <p class="qlink__cta">Preview the partner form →</p>
            </span>
            <span class="qlink__icon" aria-hidden="true">⇄</span>
        </a>
    </section>

</div><!-- /.dash -->