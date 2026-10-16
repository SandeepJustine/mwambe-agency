(function () {
    'use strict';

    function boot() {
        var shell = document.querySelector('[data-admin-shell]');
        if (!shell) return;

        var burger = shell.querySelector('.admin__burger');
        var sidebar = shell.querySelector('.admin__sidebar');
        if (!burger || !sidebar) return;

        burger.addEventListener('click', function () {
            var open = shell.classList.toggle('is-open');
            burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        });

        // Close when clicking outside on mobile
        document.addEventListener('click', function (e) {
            if (!shell.classList.contains('is-open')) return;
            if (sidebar.contains(e.target) || burger.contains(e.target)) return;
            shell.classList.remove('is-open');
            burger.setAttribute('aria-expanded', 'false');
        });
    }

    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', boot);
    else boot();
})();