<?php
$currentPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
$isCurrentPage = static function (string $path) use ($currentPath): bool {
    return $path === '/'
        ? $currentPath === '/'
        : ($currentPath === $path || str_starts_with($currentPath, rtrim($path, '/') . '/'));
};
?>
<header class="site-header" role="banner">
    <div class="container site-header__inner">

        <a class="brand" href="/" aria-label="MWAMBE Agency home">
            <img
                src="<?= e(asset('assets/img/mwambe-logo.png')) ?>"
                alt="MWAMBE Agency — Your trusted partner"
                class="brand__logo"
                width="180" height="180">
            <span class="brand__lockup" aria-hidden="true">
                <span class="brand__name">MWAMBE</span>
                <span class="brand__descriptor">Agency</span>
            </span>
        </a>

        <button
            class="nav-toggle"
            type="button"
            aria-expanded="false"
            aria-controls="primary-nav"
            aria-label="Toggle navigation menu">
            <span class="nav-toggle__bar"></span>
            <span class="nav-toggle__bar"></span>
            <span class="nav-toggle__bar"></span>
        </button>

        <nav class="primary-nav" id="primary-nav" aria-label="Primary">
            <ul class="primary-nav__list">
                <li><a href="/"<?= $isCurrentPage('/') ? ' aria-current="page"' : '' ?>>Home</a></li>
                <li><a href="/about"<?= $isCurrentPage('/about') ? ' aria-current="page"' : '' ?>>About Us</a></li>
                <li><a href="/our-work"<?= $isCurrentPage('/our-work') || str_starts_with($currentPath, '/programmes') ? ' aria-current="page"' : '' ?>>Our Work</a></li>
                <li><a href="/sectors"<?= $isCurrentPage('/sectors') ? ' aria-current="page"' : '' ?>>Sectors</a></li>
                <li><a href="/impact"<?= $isCurrentPage('/impact') ? ' aria-current="page"' : '' ?>>Our Impact</a></li>
                <li class="primary-nav__item--dropdown<?= $isCurrentPage('/news') || $isCurrentPage('/events') || $isCurrentPage('/documents') ? ' is-active' : '' ?>">
                    <a class="primary-nav__parent" href="/news"<?= $isCurrentPage('/news') ? ' aria-current="page"' : '' ?>>
                        News &amp; Activities
                        <span class="primary-nav__chevron" aria-hidden="true"></span>
                    </a>
                    <ul class="primary-nav__submenu" aria-label="News and activities">
                        <li><a href="/events"<?= $isCurrentPage('/events') ? ' aria-current="page"' : '' ?>>Events &amp; Activities</a></li>
                        <li><a href="/documents"<?= $isCurrentPage('/documents') ? ' aria-current="page"' : '' ?>>Resources &amp; Documents</a></li>
                    </ul>
                </li>
                <li><a href="/partner-with-us"<?= $isCurrentPage('/partner-with-us') ? ' aria-current="page"' : '' ?>>Partner With Us</a></li>
                <li><a href="/contact" class="btn btn--primary btn--sm"<?= $isCurrentPage('/contact') ? ' aria-current="page"' : '' ?>>Contact</a></li>
            </ul>
        </nav>
    </div>
</header>