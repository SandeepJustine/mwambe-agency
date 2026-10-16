/* =============================================================
   MWAMBE Agency — interactions
   - Homepage hero slider (3 slides, accessible)
   - Scroll reveal
   - Mobile menu toggle
   ============================================================= */
(function () {
    'use strict';

    var prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    /* ---------------- Hero slider ---------------- */
    function initSlider(root) {
        var slides = Array.prototype.slice.call(root.querySelectorAll('[data-slide]'));
        var dots   = Array.prototype.slice.call(root.querySelectorAll('[data-slider-go]'));
        var prev   = root.querySelector('[data-slider-prev]');
        var next   = root.querySelector('[data-slider-next]');
        var interval = parseInt(root.getAttribute('data-autoplay-interval') || '7000', 10);

        if (slides.length < 2) return;

        var current = 0;
        var timer = null;
        var paused = false;

        function setActive(index, announce) {
            index = ((index % slides.length) + slides.length) % slides.length;
            if (index === current) return;

            slides[current].classList.remove('is-active');
            slides[current].setAttribute('aria-hidden', 'true');

            slides[index].classList.add('is-active');
            slides[index].removeAttribute('aria-hidden');

            dots.forEach(function (d, i) {
                d.classList.toggle('is-active', i === index);
                d.setAttribute('aria-selected', i === index ? 'true' : 'false');
            });

            current = index;

            if (announce) {
                // Move focus to headline for screen reader users, without stealing visible focus
                var h = slides[index].querySelector('.hero-slide__headline');
                if (h) h.setAttribute('tabindex', '-1');
            }
        }

        function start() {
            if (prefersReducedMotion || paused) return;
            stop();
            timer = window.setInterval(function () { setActive(current + 1); }, interval);
        }
        function stop() {
            if (timer) { window.clearInterval(timer); timer = null; }
        }

        if (prev) prev.addEventListener('click', function () { setActive(current - 1); restart(); });
        if (next) next.addEventListener('click', function () { setActive(current + 1); restart(); });

        dots.forEach(function (dot) {
            dot.addEventListener('click', function () {
                var i = parseInt(dot.getAttribute('data-slider-go'), 10);
                if (!isNaN(i)) { setActive(i); restart(); }
            });
        });

        function restart() { stop(); start(); }

        // Pause on hover (pointer)
        root.addEventListener('mouseenter', function () { paused = true; stop(); });
        root.addEventListener('mouseleave', function () { paused = false; start(); });

        // Pause on keyboard focus within
        root.addEventListener('focusin',  function () { paused = true; stop(); });
        root.addEventListener('focusout', function (e) {
            if (!root.contains(e.relatedTarget)) { paused = false; start(); }
        });

        // Pause when tab is hidden
        document.addEventListener('visibilitychange', function () {
            if (document.hidden) { stop(); } else { start(); }
        });

        // Keyboard arrows when slider has focus
        root.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft')  { setActive(current - 1); restart(); }
            if (e.key === 'ArrowRight') { setActive(current + 1); restart(); }
        });

        // Touch / swipe
        var startX = null;
        root.addEventListener('touchstart', function (e) {
            startX = e.touches[0].clientX;
        }, { passive: true });
        root.addEventListener('touchend', function (e) {
            if (startX === null) return;
            var dx = e.changedTouches[0].clientX - startX;
            if (Math.abs(dx) > 50) { setActive(current + (dx < 0 ? 1 : -1)); restart(); }
            startX = null;
        }, { passive: true });

        start();
    }

    /* ---------------- Scroll reveal ---------------- */
    function initReveal() {
        var els = document.querySelectorAll('[data-reveal]');
        if (!els.length) return;
        if (prefersReducedMotion || !('IntersectionObserver' in window)) {
            els.forEach(function (el) { el.classList.add('is-revealed'); });
            return;
        }
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-revealed');
                    io.unobserve(entry.target);
                }
            });
        }, { rootMargin: '0px 0px -8% 0px', threshold: 0.08 });
        els.forEach(function (el) { io.observe(el); });
    }

    /* ---------------- Mobile menu ---------------- */
    function initMobileMenu() {
        var toggle = document.querySelector('.nav-toggle');
        if (!toggle) return;
        var nav = document.querySelector('.primary-nav');
        var links = nav ? nav.querySelectorAll('a') : [];

        function setOpen(open) {
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            document.body.classList.toggle('nav-open', open);
        }

        toggle.addEventListener('click', function () {
            setOpen(toggle.getAttribute('aria-expanded') !== 'true');
        });

        // Close on Escape
        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape' && document.body.classList.contains('nav-open')) {
                setOpen(false);
                toggle.focus();
            }
        });

        // Close when a nav link is activated
        links.forEach(function (link) {
            link.addEventListener('click', function () { setOpen(false); });
        });
    }

    /* ---------------- Boot ---------------- */
    function boot() {
        document.querySelectorAll('[data-slider]').forEach(initSlider);
        initReveal();
        initMobileMenu();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }
})();