@extends('layouts.app')
@section('title', $lesson['title'])

@push('head')
<link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ @filemtime(public_path('css/pages.css')) }}">
@endpush

@php
    $isBijoy = $lesson['lang'] === 'bijoy';
    $home = ['a','s','d','f','j','k','l',';'];
    $combining = ['ু','ূ','ৃ','ি','ী','া','ে','ৈ','্','ৗ','ঁ','ং','ঃ'];
    $show = fn ($s) => $s === '' ? '' : (in_array(mb_substr($s, 0, 1), $combining, true) ? '◌'.$s : $s);
@endphp

@section('content')
<div class="pg-wrap" x-data="trainerRun(@js(['id' => $lesson['id'], 'lang' => $lesson['lang'], 'items' => $lesson['items'], 'fingers' => $fingers, 'completeUrl' => route('learn.complete', $lesson['id'], false)]))" x-init="init()" @keydown.window="onKey($event)">
    <div style="display:flex;align-items:center;gap:.7rem;flex-wrap:wrap">
        <a href="{{ route('learn.index', ['lang' => $lesson['lang']]) }}" class="ln-btn sec" style="padding:.5rem 1rem">← পাঠ তালিকা</a>
        <h1 style="font-size:1.2rem;font-weight:800;margin:0">{{ $lesson['icon'] }} {{ $isBijoy ? 'বিজয়' : 'অভ্র' }} · পাঠ {{ \App\Support\Bn::digits($lesson['no']) }}: {{ $lesson['title'] }}</h1>
    </div>

    <div class="ln-warn" x-show="touch" x-cloak>📱 এই অনুশীলন কম্পিউটারের কীবোর্ডে করার জন্য। ফোনে কী চাপা যাবে না — কম্পিউটারে খুলুন।</div>
    @if ($lesson['note'])
        <div class="ln-note">💡 {{ $lesson['note'] }}</div>
    @endif

    <div class="ln-stage anim-fade-up">
        <div x-show="!done">
            <div class="ln-bar"><i :style="'width:' + progress + '%'"></i></div>

            <div class="ln-now">
                <div class="bn" :class="pop && 'pop'" x-text="current.bn === ' ' ? '␣ স্পেস' : current.bn"></div>
                <div class="ln-seq">
                    <template x-for="(k, i) in keys" :key="i">
                        <span class="ln-k" :class="i < ki ? 'done' : (i === ki ? 'cur' + (flash ? ' wrong' : '') : '')">
                            <small x-show="needShift(k)">Shift+</small><span x-text="chip(k)"></span>
                        </span>
                    </template>
                </div>
            </div>

            <div class="ln-strip">
                <template x-for="(it, i) in items" :key="i">
                    <span class="ln-chipb" :class="i < ii ? 'done' : (i === ii ? 'cur' : '')" x-text="it.bn === ' ' ? '␣' : it.bn"></span>
                </template>
            </div>

            <div class="ln-out"><small>আপনার লেখা:</small><span x-text="out"></span></div>

            <div class="ln-say">
                <div class="ln-say-t">
                    <span><b x-text="current.bn === ' ' ? 'স্পেস' : '“' + current.bn + '”'"></b> লিখতে কীবোর্ডে চাপুন:</span>
                    <template x-for="(k, i) in keys" :key="'s' + i">
                        <span class="ln-say-k" :class="i < ki ? 'done' : (i === ki ? 'cur' : '')">
                            <template x-if="i > 0"><em>তারপর</em></template>
                            <kbd x-text="(needShift(k) ? 'Shift + ' : '') + chip(k)"></kbd>
                        </span>
                    </template>
                </div>
                <div class="ln-say-s" x-text="hint"></div>
            </div>

            <div class="gd-kb ln-kb ln-kb2">
                @foreach ($layout as $ri => $row)
                    <div class="gd-row r{{ $ri }}">
                        @if ($ri === 3)<span class="gd-key ln-shift f-pinky" :class="shiftSide === 'L' && 'next'">⇧ Shift</span>@endif
                        @foreach ($row as $i => [$k, $nm, $sh])
                            <span class="gd-key ln-key f-{{ $types[$i] }} {{ in_array($k, $home, true) ? 'home' : '' }} {{ in_array($k, ['f','j'], true) ? 'bump' : '' }}"
                                  :class="[nextBase === @js($k) && 'next', badKey === @js($k) && 'bad']">
                                @if ($isBijoy)
                                    <b class="en">{{ strtoupper($k) }}</b><i class="sh">{{ $show($sh) }}</i><i class="nm">{{ $show($nm) }}</i>
                                @else
                                    <span class="only">{{ strtoupper($k) }}</span>
                                @endif
                            </span>
                        @endforeach
                        @if ($ri === 3)<span class="gd-key ln-shift f-pinky" :class="shiftSide === 'R' && 'next'">⇧ Shift</span>@endif
                    </div>
                @endforeach
                <div class="gd-row"><span class="gd-key gd-space f-thumb" :class="nextBase === ' ' && 'next'">স্পেস</span></div>
            </div>
        </div>

        <div class="ln-sum" x-show="done" x-cloak>
            <div class="big">🎉</div>
            <h2>দারুণ! পাঠ সম্পন্ন</h2>
            <div class="ln-stats">
                <div class="ln-stat"><b x-text="accuracy + '%'">0%</b><small>নির্ভুলতা</small></div>
                <div class="ln-stat"><b x-text="mistakes">0</b><small>ভুল চাপ</small></div>
            </div>
            <p class="ln-hint" x-show="weak.length" x-cloak>আরেকটু অনুশীলন দরকার: <b x-text="weak.join('   ')"></b></p>
            <div class="ln-out" style="margin-top:.6rem"><small>আপনার লেখা:</small><span x-text="out"></span></div>
            <div class="ln-actions">
                <button type="button" class="ln-btn sec" @click="restart()">🔁 আবার করুন</button>
                @if ($next)
                    <a href="{{ route('learn.show', $next['id']) }}" class="ln-btn pri">পরের পাঠ: {{ $next['title'] }} →</a>
                @else
                    <a href="{{ route('practice.index') }}" class="ln-btn pri">🎯 এবার টাইপিং শুরু-তে যান →</a>
                @endif
            </div>
        </div>
    </div>

    @unless ($isBijoy)
        @include('learn.avro-chart')
    @endunless
</div>

<script>
function trainerRun(cfg) {
    const SHIFTED = {'!':'1','@':'2','#':'3','$':'4','%':'5','^':'6','&':'7','*':'8','(':'9',')':'0','_':'-','+':'=','|':'\\',':':';','"':"'",'<':',','>':'.','?':'/','~':'`','{':'[','}':']'};
    const LEFT = 'qwertasdfgzxcvb12345`';
    const UNSHIFT = {}; Object.keys(SHIFTED).forEach(k => { UNSHIFT[SHIFTED[k]] = k; });
    const CODES = {Minus: '-', Equal: '=', BracketLeft: '[', BracketRight: ']', Backslash: '\\', Semicolon: ';', Quote: "'", Comma: ',', Period: '.', Slash: '/', Backquote: '`', Space: ' '};
    const keyOf = (ch) => {
        if (SHIFTED[ch]) return {k: SHIFTED[ch], s: true};
        if (/^[A-Z]$/.test(ch)) return {k: ch.toLowerCase(), s: true};
        return {k: ch, s: false};
    };
    // কোন কী চাপা হলো — কীবোর্ড লেআউট/ভাষা (Avro, Bijoy সফটওয়্যার, বাংলা IME) যা-ই চালু থাক, ফিজিক্যাল কী (e.code) দেখে বের করা হয়
    const typedOf = (e) => {
        let b = null;
        if (/^Key[A-Z]$/.test(e.code || '')) b = e.code.charAt(3).toLowerCase();
        else if (/^Digit[0-9]$/.test(e.code || '')) b = e.code.charAt(5);
        else if (CODES[e.code]) b = CODES[e.code];
        if (b === null) return (e.key && e.key.length === 1) ? e.key : null;
        if (e.shiftKey) return /^[a-z]$/.test(b) ? b.toUpperCase() : (UNSHIFT[b] || b);
        return b;
    };
    return {
        items: cfg.items, lang: cfg.lang, id: cfg.id, fingers: cfg.fingers,
        ii: 0, ki: 0, out: '', mistakes: 0, hits: 0, wrong: {}, flash: false, badKey: '', pop: false,
        done: false, accuracy: 0, weak: [], caps: false, touch: false, saved: false,
        init() { this.touch = window.matchMedia && window.matchMedia('(pointer: coarse)').matches && !window.matchMedia('(hover: hover)').matches; },
        get current() { return this.items[this.ii] || {bn: '', keys: ''}; },
        get keys() { return Array.from(this.current.keys); },
        get expected() { return this.keys[this.ki] || ''; },
        get nextBase() { return this.done ? '' : keyOf(this.expected).k; },
        get shiftSide() { const x = keyOf(this.expected); return x.s ? (LEFT.includes(x.k) ? 'R' : 'L') : ''; },
        get total() { return this.items.reduce((a, it) => a + Array.from(it.keys).length, 0); },
        get progress() { return Math.min(100, Math.round(this.hits / this.total * 100)); },
        needShift(ch) { return /^[A-Z]$/.test(ch); },
        label(ch) { const x = keyOf(ch); return x.k === ' ' ? 'Space' : (/^[a-z]$/.test(x.k) ? x.k.toUpperCase() : x.k); },
        chip(ch) { return ch === ' ' ? 'Space' : ch; },
        get hint() {
            if (this.done || !this.expected) return '';
            const x = keyOf(this.expected);
            const f = this.fingers[x.k] || (x.k === '`' ? '১-এর বামের ` কী' : '');
            let t = '🖐️ ' + f;
            if (x.s) t += ' · Shift চাপুন ' + (LEFT.includes(x.k) ? 'ডান' : 'বাম') + ' কনিষ্ঠা দিয়ে';
            return t;
        },
        onKey(e) {
            if (this.done || e.ctrlKey || e.altKey || e.metaKey) return;
            const t = e.target; if (t && (/^(INPUT|TEXTAREA|SELECT)$/.test(t.tagName) || t.isContentEditable)) return;
            const typed = typedOf(e);
            if (typed === null) return;                // Shift, Tab, তীর ইত্যাদি উপেক্ষা
            e.preventDefault();                        // স্পেসে পেজ স্ক্রল বন্ধ, IME আটকানো
            if (typed === this.expected) {
                this.hits++; this.ki++; this.flash = false;
                if (this.ki >= this.keys.length) { this.out += this.current.bn; this.ii++; this.ki = 0; this.pop = true; setTimeout(() => { this.pop = false; }, 260); if (this.ii >= this.items.length) this.finish(); }
            } else {
                this.mistakes++;
                const x = keyOf(this.expected), nm = (x.s ? '⇧' : '') + this.label(this.expected);
                this.wrong[nm] = (this.wrong[nm] || 0) + 1;
                this.flash = true; this.badKey = keyOf(typed).k;
                setTimeout(() => { this.flash = false; this.badKey = ''; }, 280);
            }
        },
        finish() {
            const total = this.hits + this.mistakes;
            this.accuracy = total ? Math.round(this.hits / total * 100) : 100;
            this.weak = Object.entries(this.wrong).sort((a, b) => b[1] - a[1]).slice(0, 4).map(x => x[0]);
            this.done = true;
            try { const k = 'tm_lessons_done', a = JSON.parse(localStorage.getItem(k) || '[]'); if (!a.includes(this.id)) { a.push(this.id); localStorage.setItem(k, JSON.stringify(a)); } } catch (e) {}
            try {
                fetch(cfg.completeUrl, {method: 'POST', credentials: 'same-origin', headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') || {}).content || ''}, body: JSON.stringify({accuracy: this.accuracy})}).catch(() => {});
            } catch (e) {}
        },
        restart() { this.ii = 0; this.ki = 0; this.out = ''; this.mistakes = 0; this.hits = 0; this.wrong = {}; this.flash = false; this.done = false; this.weak = []; },
    };
}
</script>
@endsection
