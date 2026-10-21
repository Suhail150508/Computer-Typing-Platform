/**
 * হালকা কনফেটি ইফেক্ট — কোনো বাইরের লাইব্রেরি ছাড়াই। কল করুন: window.fireConfetti()
 * টেস্ট পাস করার পর উদযাপনের জন্য ব্যবহার হয় (দেখুন results/show.blade.php)।
 */
(function () {
    'use strict';

    var colors = ['#f43f5e', '#f59e0b', '#22c55e', '#3b82f6', '#a855f7', '#facc15'];

    window.fireConfetti = function (count) {
        count = count || 90;
        if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            return; // মোশন-সেনসিটিভ ইউজারের জন্য অ্যানিমেশন বাদ
        }

        for (var i = 0; i < count; i++) {
            (function () {
                var piece = document.createElement('div');
                piece.className = 'confetti-piece';
                piece.style.left = (Math.random() * 100) + 'vw';
                piece.style.background = colors[Math.floor(Math.random() * colors.length)];
                piece.style.animationDuration = (2.2 + Math.random() * 1.6) + 's';
                piece.style.animationDelay = (Math.random() * 0.6) + 's';
                piece.style.opacity = String(0.75 + Math.random() * 0.25);
                document.body.appendChild(piece);
                setTimeout(function () { piece.remove(); }, 4500);
            })();
        }
    };
})();
