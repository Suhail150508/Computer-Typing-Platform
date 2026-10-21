/**
 * TYPING MOJA — শীট টাইপিং ইঞ্জিন (ক্লায়েন্ট সাইড)
 * - প্রথম অক্ষর টাইপে টাইমার শুরু; সময় শেষ হলে নিজে থেকেই জমা
 * - Tab = ডানের ঘর, Enter/↓ = নিচের ঘর, ↑ = উপরের ঘর
 * - ফোকাস করা ঘরের সাথে মূল শীটের একই ঘর হলুদ হয়ে যায়
 * - কপি/পেস্ট/কাট/ড্রপ বন্ধ; চূড়ান্ত ফলাফল সবসময় সার্ভারে হিসাব হয়
 */
(function () {
    'use strict';

    var root = document.getElementById('sheet-root');
    if (!root) { return; }

    var duration = parseInt(root.getAttribute('data-duration'), 10) || 180;
    var ROWS = parseInt(root.getAttribute('data-rows'), 10) || 0;
    var COLS = parseInt(root.getAttribute('data-cols'), 10) || 0;

    var timerEl = document.getElementById('timer');
    var wpmEl = document.getElementById('live-wpm');
    var cellsEl = document.getElementById('live-cells');
    var form = document.getElementById('submit-form');
    var dialog = document.getElementById('confirm-dialog');
    var refScroll = document.getElementById('ref-scroll');
    var inputs = Array.prototype.slice.call(document.querySelectorAll('#grid input[data-r]'));
    var refCells = {};
    Array.prototype.forEach.call(document.querySelectorAll('#ref-table td.ref'), function (td) {
        refCells[td.getAttribute('data-r') + ',' + td.getAttribute('data-c')] = td;
    });

    var byPos = {};
    inputs.forEach(function (inp) { byPos[inp.getAttribute('data-r') + ',' + inp.getAttribute('data-c')] = inp; });

    var startedAt = null;
    var ticker = null;
    var finished = false;
    var samples = []; // প্রতি সেকেন্ডে মোট টাইপ করা অক্ষর (স্পেস ছাড়া) — স্পিড চার্টের জন্য
    var activeRef = null;

    function pad(n) { return (n < 10 ? '0' : '') + n; }
    function fmt(sec) { sec = Math.max(0, sec); return pad(Math.floor(sec / 60)) + ':' + pad(sec % 60); }
    function elapsedSec() { return startedAt ? Math.floor((Date.now() - startedAt) / 1000) : 0; }

    function renderTimer() {
        var remain = duration - elapsedSec();
        timerEl.textContent = fmt(remain);
        timerEl.classList.toggle('timer-warn', startedAt !== null && remain <= 30);
        return remain;
    }

    function totalChars() {
        var n = 0;
        inputs.forEach(function (i) { n += Array.from(i.value.replace(/\s+/g, '')).length; });
        return n;
    }

    function updateLive() {
        var filled = 0;
        inputs.forEach(function (i) {
            var has = i.value.trim() !== '';
            if (has) { filled++; }
            i.parentNode.classList.toggle('filled', has);
        });
        cellsEl.textContent = filled;

        var e = elapsedSec();
        if (e >= 3) { wpmEl.textContent = Math.round((totalChars() / 5) / (e / 60)); }
    }

    function recordSamples() {
        if (!startedAt) { return; }
        var sec = Math.min(duration, elapsedSec());
        if (samples.length >= sec) { return; }
        var chars = totalChars();
        while (samples.length < sec) { samples.push(chars); }
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

    function collect() {
        var grid = [];
        for (var r = 0; r < ROWS; r++) {
            var row = [];
            for (var c = 0; c < COLS; c++) {
                var inp = byPos[r + ',' + c];
                row.push(inp ? inp.value : '');
            }
            grid.push(row);
        }
        return grid;
    }

    function finish() {
        if (finished) { return; }
        finished = true;
        if (ticker) { clearInterval(ticker); }
        inputs.forEach(function (i) { i.readOnly = true; });
        window.removeEventListener('beforeunload', warnLeave);

        recordSamples();
        form.querySelector('[name=cells]').value = JSON.stringify(collect());
        form.querySelector('[name=elapsed]').value = Math.min(duration, Math.max(1, Math.ceil((Date.now() - (startedAt || Date.now())) / 1000)));
        form.querySelector('[name=speed_samples]').value = JSON.stringify(samples);
        form.submit();
    }

    // ---- মূল শীটে বর্তমান ঘর হাইলাইট ----
    function highlight(r, c) {
        var td = refCells[r + ',' + c];
        if (activeRef) { activeRef.classList.remove('on'); }
        activeRef = td || null;
        if (!td) { return; }
        td.classList.add('on');
        var box = refScroll.getBoundingClientRect();
        var t = td.getBoundingClientRect();
        if (t.top < box.top + 40 || t.bottom > box.bottom - 8) {
            refScroll.scrollTo({ top: Math.max(0, refScroll.scrollTop + (t.top - box.top) - box.height / 2), behavior: 'smooth' });
        }
        if (t.left < box.left + 40 || t.right > box.right - 8) {
            refScroll.scrollTo({ left: Math.max(0, refScroll.scrollLeft + (t.left - box.left) - box.width / 3), behavior: 'smooth' });
        }
    }

    function markDone(inp) {
        var td = refCells[inp.getAttribute('data-r') + ',' + inp.getAttribute('data-c')];
        if (td) { td.classList.toggle('done', inp.value.trim() !== ''); }
    }

    function focusAt(r, c) {
        var inp = byPos[r + ',' + c];
        if (inp) { inp.focus(); inp.select(); return true; }
        return false;
    }

    inputs.forEach(function (inp) {
        var r = parseInt(inp.getAttribute('data-r'), 10);
        var c = parseInt(inp.getAttribute('data-c'), 10);

        inp.addEventListener('focus', function () { highlight(r, c); });
        inp.addEventListener('input', function () { start(); markDone(inp); updateLive(); });

        inp.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === 'ArrowDown') {
                e.preventDefault();
                // নিচের ঘর; কলামের শেষ হলে পরের কলামের প্রথম ঘর
                if (!focusAt(r + 1, c)) { focusAt(0, c + 1); }
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                focusAt(r - 1, c);
            }
        });

        ['paste', 'cut', 'copy', 'drop', 'contextmenu'].forEach(function (evt) {
            inp.addEventListener(evt, function (e) { e.preventDefault(); });
        });
    });
    ['copy', 'cut', 'contextmenu', 'dragstart'].forEach(function (evt) {
        refScroll.addEventListener(evt, function (e) { e.preventDefault(); });
    });

    // ফিনিশ + নিশ্চিতকরণ
    document.getElementById('btn-finish').addEventListener('click', function () {
        if (typeof dialog.showModal === 'function') { dialog.showModal(); } else if (window.confirm('টেস্ট শেষ করতে চান?')) { finish(); }
    });
    document.getElementById('btn-confirm').addEventListener('click', function () { dialog.close(); finish(); });
    document.getElementById('btn-cancel').addEventListener('click', function () { dialog.close(); if (inputs[0]) { inputs[0].focus(); } });

    // Sheet বাটন: মূল শীট দেখান/লুকান
    var wrap = document.getElementById('ref-wrap');
    document.getElementById('btn-content').addEventListener('click', function () { wrap.classList.toggle('hidden'); });

    // ফন্ট সাইজ
    var size = 16;
    function applySize() { root.style.setProperty('--sheet-font', size + 'px'); }
    document.getElementById('btn-font-up').addEventListener('click', function () { size = Math.min(26, size + 2); applySize(); });
    document.getElementById('btn-font-down').addEventListener('click', function () { size = Math.max(12, size - 2); applySize(); });
    applySize();

    renderTimer();
    updateLive();
    if (inputs[0]) { inputs[0].focus(); }
})();
