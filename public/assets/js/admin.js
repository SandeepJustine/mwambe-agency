(function () {
    'use strict';

    function boot() {
        var shell = document.querySelector('[data-admin-shell]');
        if (!shell) return;

        var burger = shell.querySelector('.admin__burger');
        var sidebar = shell.querySelector('.admin__sidebar');
        if (!burger || !sidebar) return;

        var isMobile = window.matchMedia('(max-width: 900px)');

        function setScrollLock(lock) {
            document.body.classList.toggle('admin--scroll-lock', lock);
        }

        function open() {
            shell.classList.add('is-open');
            burger.setAttribute('aria-expanded', 'true');
            if (isMobile.matches) setScrollLock(true);
        }

        function close() {
            shell.classList.remove('is-open');
            burger.setAttribute('aria-expanded', 'false');
            if (isMobile.matches) setScrollLock(false);
        }

        burger.addEventListener('click', function () {
            shell.classList.contains('is-open') ? close() : open();
        });

        // Close when clicking outside the sidebar/burger on mobile
        document.addEventListener('click', function (e) {
            if (!shell.classList.contains('is-open')) return;
            if (sidebar.contains(e.target) || burger.contains(e.target)) return;
            close();
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && shell.classList.contains('is-open')) close();
        });

        // Reset state when crossing the mobile breakpoint
        isMobile.addEventListener('change', function (m) {
            if (!m.matches) return;
            shell.classList.remove('is-open');
            burger.setAttribute('aria-expanded', 'false');
            setScrollLock(false);
        });
    }

    /* Auto-generate slug from name on category edit forms */
    function initCategorySlug() {
        var name = document.getElementById('name');
        var slug = document.getElementById('slug');
        if (!name || !slug) return;

        function sync() {
            if (slug.dataset.manual === '1') return;
            slug.value = name.value
                .toLowerCase()
                .trim()
                .replace(/[^a-z0-9-]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        name.addEventListener('input', sync);

        // Allow re-typing the slug manually
        slug.addEventListener('focus', function () { slug.dataset.manual = '1'; });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { boot(); initCategorySlug(); });
    } else {
        boot();
        initCategorySlug();
    }
})();
