/**
 * .count-up ক্লাসের যেকোনো এলিমেন্টের সংখ্যা 0 থেকে data-target পর্যন্ত অ্যানিমেট করে গুনে ওঠে।
 * ব্যবহার: <span class="count-up" data-target="42" data-decimals="0">0</span>
 * পুরো অ্যাপে একবারই লোড হয় (base layout থেকে), তাই কোনো পেজে আলাদা স্ক্রিপ্ট লেখার দরকার নেই।
 */
(function () {
    'use strict';

    function animate(el) {
        var target = parseFloat(el.getAttribute('data-target')) || 0;
        var decimals = parseInt(el.getAttribute('data-decimals'), 10) || 0;
        var duration = 900;
        var start = null;

        function step(ts) {
            if (!start) { start = ts; }
            var progress = Math.min(1, (ts - start) / duration);
            var eased = 1 - Math.pow(1 - progress, 3);
            el.textContent = (target * eased).toFixed(decimals);
            if (progress < 1) { requestAnimationFrame(step); }
        }

        requestAnimationFrame(step);
    }

    function init() {
        document.querySelectorAll('.count-up:not([data-counted])').forEach(function (el) {
            el.setAttribute('data-counted', '1');
            animate(el);
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
