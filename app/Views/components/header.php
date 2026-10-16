<header class="site-header" role="banner">
    <div class="container site-header__inner">

        <a class="brand" href="/" aria-label="MWAMBE Agency home">
            <img
                src="<?= e(asset('assets/img/mwambe-logo.png')) ?>"
                alt="MWAMBE Agency — Your trusted partner"
                class="brand__logo"
                width="180" height="180">
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
                <li><a href="/">Home</a></li>
                <li><a href="/about">About Us</a></li>
                <li><a href="/our-work">Our Work</a></li>
                <li><a href="/sectors">Sectors</a></li>
                <li><a href="/impact">Our Impact</a></li>
                <li><a href="/news">News &amp; Activities</a></li>
                <li><a href="/partner-with-us">Partner With Us</a></li>
                <li><a href="/contact" class="btn btn--primary btn--sm">Contact</a></li>
            </ul>
        </nav>
    </div>
</header>