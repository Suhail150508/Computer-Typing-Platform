/**
 * TypingMoja — টাইপিং টেস্ট ইঞ্জিন (ক্লায়েন্ট সাইড)
 * - প্রথম কি-স্ট্রোকে টাইমার শুরু, সময় শেষ হলে নিজে থেকেই সাবমিট
 * - বর্তমান শব্দ হাইলাইট + প্যাসেজ অটো-স্ক্রল
 * - কপি/পেস্ট/কাট/ড্রপ বন্ধ (পরীক্ষার পরিবেশ)
 * চূড়ান্ত ফলাফল সবসময় সার্ভারে হিসাব হয়; এখানকার WPM শুধু লাইভ আনুমানিক।
 */
(function () {
    'use strict';

    var root = document.getElementById('typing-root');
    if (!root) { return; }

    var duration = parseInt(root.getAttribute('data-duration'), 10) || 300;
    var area = document.getElementById('typing-area');
    var passage = document.getElementById('passage');
    var timerEl = document.getElementById('timer');
    var wpmEl = document.getElementById('live-wpm');
    var wordsEl = document.getElementById('live-words');
    var form = document.getElementById('submit-form');
    var dialog = document.getElementById('confirm-dialog');
    var words = Array.prototype.slice.call(document.querySelectorAll('#passage .pw'));

    var startedAt = null;
    var ticker = null;
    var finished = false;
    var current = -1;
    var samples = []; // স্পিড চার্টের জন্য: প্রতি সেকেন্ডে মোট টাইপ করা অক্ষর (স্পেস ছাড়া)

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function fmt(sec) { sec = Math.max(0, sec); return pad(Math.floor(sec / 60)) + ':' + pad(sec % 60); }
    function elapsedSec() { return startedAt ? Math.floor((Date.now() - startedAt) / 1000) : 0; }

    function renderTimer() {
        var remain = duration - elapsedSec();
        timerEl.textContent = fmt(remain);
        timerEl.classList.toggle('timer-warn', startedAt !== null && remain <= 30);
        return remain;
    }

    function stripSpaces(s) { return s.replace(/\s+/g, ''); }

    function recordSamples() {
        if (!startedAt) { return; }
        var sec = Math.min(duration, elapsedSec());
        if (samples.length >= sec) { return; }
        var chars = Array.from(stripSpaces(area.value)).length;
        while (samples.length < sec) { samples.push(chars); }
    }

    function updateLive() {
        var value = area.value;
        var typedWords = value.trim() === '' ? 0 : value.trim().split(/\s+/).length;
        wordsEl.textContent = typedWords;

        var e = elapsedSec();
        if (e >= 3) {
            var chars = Array.from(stripSpaces(value)).length; // কোড পয়েন্ট গণনা (বাংলার জন্য জরুরি)
            wpmEl.textContent = Math.round((chars / 5) / (e / 60));
        }

        // বর্তমান শব্দ হাইলাইট
        var idx = typedWords;
        if (typedWords > 0 && !/\s$/.test(value)) { idx = typedWords - 1; }
        idx = Math.min(idx, words.length - 1);
        if (idx !== current) {
            if (words[current]) { words[current].classList.remove('cur'); }
            current = idx;
            var w = words[current];
            if (w) {
                w.classList.add('cur');
                var top = w.offsetTop;
                if (top - passage.scrollTop > passage.clientHeight - 48 || top < passage.scrollTop) {
                    passage.scrollTo({ top: Math.max(0, top - 40), behavior: 'smooth' });
                }
            }
        }
    }

    function start() {
        if (startedAt !== null) { return; }
        startedAt = Date.now();
        ticker = setInterval(function () {
            var remain = renderTimer();
            updateLive();
            recordSamples();
            if (remain <= 0) { finish(); }
        }, 250);
        window.addEventListener('beforeunload', warnLeave);
    }

    function warnLeave(e) {
        if (finished) { return; }
        e.preventDefault();
        e.returnValue = '';
    }

    function finish() {
        if (finished) { return; }
        finished = true;
        if (ticker) { clearInterval(ticker); }
        area.readOnly = true;
        window.removeEventListener('beforeunload', warnLeave);

        recordSamples();
        form.querySelector('[name=typed_text]').value = area.value;
        var sp = form.querySelector('[name=speed_samples]');
        if (sp) { sp.value = JSON.stringify(samples); }
        form.querySelector('[name=elapsed]').value = Math.min(duration, Math.max(1, Math.ceil((Date.now() - (startedAt || Date.now())) / 1000)));
        form.submit();
    }

    // ---- ইভেন্ট ----
    area.addEventListener('input', function () { start(); updateLive(); });
    ['paste', 'cut', 'copy', 'drop', 'contextmenu'].forEach(function (evt) {
        area.addEventListener(evt, function (e) { e.preventDefault(); });
    });
    ['copy', 'cut', 'contextmenu', 'dragstart'].forEach(function (evt) {
        passage.addEventListener(evt, function (e) { e.preventDefault(); });
    });

    // ফিনিশ বাটন + নিশ্চিতকরণ ডায়ালগ
    document.getElementById('btn-finish').addEventListener('click', function () {
        if (typeof dialog.showModal === 'function') { dialog.showModal(); } else if (window.confirm('টেস্ট শেষ করতে চান?')) { finish(); }
    });
    document.getElementById('btn-confirm').addEventListener('click', function () { dialog.close(); finish(); });
    document.getElementById('btn-cancel').addEventListener('click', function () { dialog.close(); area.focus(); });

    // Content বাটন: প্যাসেজ দেখান/লুকান
    var wrap = document.getElementById('passage-wrap');
    document.getElementById('btn-content').addEventListener('click', function () {
        wrap.classList.toggle('hidden');
        area.focus();
    });

    // ফন্ট সাইজ
    var size = 18;
    function applySize() { passage.style.fontSize = size + 'px'; area.style.fontSize = size + 'px'; }
    document.getElementById('btn-font-up').addEventListener('click', function () { size = Math.min(30, size + 2); applySize(); });
    document.getElementById('btn-font-down').addEventListener('click', function () { size = Math.max(14, size - 2); applySize(); });
    applySize();

    renderTimer();
    updateLive();
    area.focus();
})();
