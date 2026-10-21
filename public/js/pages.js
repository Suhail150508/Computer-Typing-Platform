/* TypingMoja — পেজ অ্যানিমেশন (রিভিল, টিল্ট, টাইপরাইটার)। JS বন্ধ থাকলেও পেজ ঠিকমতো দেখা যায়। */
(function () {
    'use strict';
    var root = document.documentElement;
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    var fine = window.matchMedia && window.matchMedia('(hover: hover) and (pointer: fine)').matches;

    // ---- স্ক্রল-রিভিল ----
    var items = document.querySelectorAll('.pg-reveal');
    function reveal(el) {
        if (el.classList.contains('is-in')) { return; }
        el.classList.add('is-in');
        var d = parseInt(el.style.getPropertyValue('--d'), 10) || 0;
        setTimeout(function () { el.classList.add('is-done'); }, 750 + d);
    }
    if (items.length && 'IntersectionObserver' in window && !reduce) {
        root.classList.add('pg-js');
        var io = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) { reveal(e.target); io.unobserve(e.target); } });
        }, { threshold: 0.08, rootMargin: '0px 0px -30px 0px' });
        items.forEach(function (el) { io.observe(el); });
        // নিরাপত্তা: কোনো কারণে অবজার্ভার না চললেও ২ সেকেন্ড পর সব দেখাবে
        setTimeout(function () { items.forEach(reveal); }, 2500);
    }

    // ---- কার্সর-অনুসারী আলো + হালকা টিল্ট (শুধু মাউসে) ----
    if (fine && !reduce) {
        document.querySelectorAll('.pg-tilt').forEach(function (el) {
            el.addEventListener('pointermove', function (ev) {
                var r = el.getBoundingClientRect();
                var x = (ev.clientX - r.left) / r.width, y = (ev.clientY - r.top) / r.height;
                el.style.setProperty('--mx', (x * 100) + '%');
                el.style.setProperty('--my', (y * 100) + '%');
                el.style.setProperty('--ry', ((x - 0.5) * 5) + 'deg');
                el.style.setProperty('--rx', ((0.5 - y) * 4) + 'deg');
            });
            el.addEventListener('pointerleave', function () {
                el.style.setProperty('--rx', '0deg'); el.style.setProperty('--ry', '0deg');
            });
        });
    }

    // ---- টাইপরাইটার লাইন (data-phrases="a|b|c") ----
    var line = document.querySelector('[data-typewriter]');
    if (line) {
        var phrases = (line.getAttribute('data-typewriter') || '').split('|').filter(Boolean);
        var target = line.querySelector('.pg-tw');
        if (phrases.length && target) {
            if (reduce) { target.textContent = phrases[0]; return; }
            var pi = 0, ci = 0, del = false;
            (function tick() {
                var full = phrases[pi];
                ci += del ? -1 : 1;
                target.textContent = full.slice(0, ci);
                var wait = del ? 28 : 55;
                if (!del && ci === full.length) { del = true; wait = 1800; }
                else if (del && ci === 0) { del = false; pi = (pi + 1) % phrases.length; wait = 350; }
                setTimeout(tick, wait);
            })();
        }
    }
})();
