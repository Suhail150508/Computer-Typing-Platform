@extends('layouts.app')
@section('title', $lesson['title'])

@push('head')
<link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ @filemtime(public_path('css/pages.css')) }}">
@endpush

@php
    $isEn = $lesson['lang'] === 'english';
    $kbRows = ['qwertyuiop', 'asdfghjkl;', 'zxcvbnm,./'];
    $types = ['pinky', 'ring', 'middle', 'index', 'index', 'index', 'index', 'middle', 'ring', 'pinky'];
    $home = ['a','s','d','f','j','k','l',';'];
@endphp

@section('content')
<div class="pg-wrap" x-data="lessonRun(@js(['id' => $lesson['id'], 'lang' => $lesson['lang'], 'text' => $lesson['text']]))" x-init="init()">
    <div style="display:flex;align-items:center;gap:.7rem;flex-wrap:wrap">
        <a href="{{ route('learn.index', ['lang' => $lesson['lang']]) }}" class="ln-btn sec" style="padding:.5rem 1rem">← পাঠ তালিকা</a>
        <h1 style="font-size:1.25rem;font-weight:800;margin:0">{{ $lesson['icon'] }} পাঠ {{ \App\Support\Bn::digits($lesson['no']) }}: {{ $lesson['title'] }}</h1>
    </div>

    <div class="ln-stage anim-fade-up">
        <div x-show="!done">
            <div class="ln-bar"><i :style="'width:' + progress + '%'"></i></div>

            <div class="ln-text" :class="focused && 'focus'" @click="focus()">
                <template x-for="(c, i) in chars" :key="i">
                    <span class="ln-ch" :class="cls(i)" x-text="c"></span>
                </template>
            </div>

            @if ($isEn)
                <input type="text" x-ref="inp" class="ln-input" autocomplete="off" autocapitalize="off" autocorrect="off" spellcheck="false" @input="onEnInput($event)" @focus="focused = true" @blur="focused = false" aria-label="টাইপ করুন">
                <p class="ln-hint" x-text="focused ? nextHint : '👆 টাইপ শুরু করতে লেখার জায়গায় ক্লিক করুন'"></p>

                <div class="gd-kb ln-kb">
                    @foreach ($kbRows as $ri => $row)
                        <div class="gd-row r{{ $ri + 1 }}">
                            @foreach (str_split($row) as $i => $k)
                                <span class="gd-key f-{{ $types[$i] }} {{ in_array($k, $home, true) ? 'home' : '' }} {{ in_array($k, ['f','j'], true) ? 'bump' : '' }}" :class="nextKey === '{{ $k }}' && 'next'">{{ strtoupper($k) }}</span>
                            @endforeach
                        </div>
                    @endforeach
                    <div class="gd-row"><span class="gd-key gd-space f-thumb" :class="nextKey === ' ' && 'next'">স্পেস</span></div>
                </div>
            @else
                <textarea x-ref="inp" class="ln-area" x-model="typed" @input="watchBn()" @focus="focused = true" @blur="focused = false" autocomplete="off" spellcheck="false" placeholder="এখানে ক্লিক করে উপরের লেখাটি টাইপ করুন..." aria-label="টাইপ করুন"></textarea>
                <p class="ln-hint">উপরের লেখা দেখে টাইপ করুন। সবুজ = ঠিক, লাল = ভুল (ব্যাকস্পেস দিয়ে ঠিক করতে পারেন)।</p>
            @endif
        </div>

        <div class="ln-sum" x-show="done" x-cloak>
            <div class="big">🎉</div>
            <h2>দারুণ! পাঠ সম্পন্ন</h2>
            <div class="ln-stats">
                <div class="ln-stat"><b x-text="accuracy + '%'">0%</b><small>নির্ভুলতা</small></div>
                <div class="ln-stat"><b x-text="mistakes">0</b><small>ভুল চাপ</small></div>
            </div>
            <p class="ln-hint" x-show="weak.length" x-cloak>আরেকটু অনুশীলন দরকার: <b x-text="weak.join('  ')"></b></p>
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
</div>

<script>
function lessonRun(cfg) {
    const FINGERS = {
        q:'বাম কনিষ্ঠা', a:'বাম কনিষ্ঠা', z:'বাম কনিষ্ঠা', w:'বাম অনামিকা', s:'বাম অনামিকা', x:'বাম অনামিকা',
        e:'বাম মধ্যমা', d:'বাম মধ্যমা', c:'বাম মধ্যমা', r:'বাম তর্জনী', t:'বাম তর্জনী', f:'বাম তর্জনী', g:'বাম তর্জনী', v:'বাম তর্জনী', b:'বাম তর্জনী',
        y:'ডান তর্জনী', u:'ডান তর্জনী', h:'ডান তর্জনী', j:'ডান তর্জনী', n:'ডান তর্জনী', m:'ডান তর্জনী',
        i:'ডান মধ্যমা', k:'ডান মধ্যমা', ',':'ডান মধ্যমা', o:'ডান অনামিকা', l:'ডান অনামিকা', '.':'ডান অনামিকা',
        p:'ডান কনিষ্ঠা', ';':'ডান কনিষ্ঠা', '/':'ডান কনিষ্ঠা', ' ':'যেকোনো বুড়ো আঙুল'
    };
    return {
        lang: cfg.lang, id: cfg.id, text: cfg.text, chars: Array.from(cfg.text),
        pos: 0, typed: '', mistakes: 0, hits: 0, wrong: {}, flash: false, done: false, focused: false,
        accuracy: 0, weak: [],
        init() { this.$nextTick(() => this.focus()); },
        focus() { if (this.$refs.inp) this.$refs.inp.focus(); },
        get isEn() { return this.lang === 'english'; },
        get expected() { return this.chars[this.pos] || ''; },
        get nextKey() { return this.isEn ? this.expected.toLowerCase() : ''; },
        get nextHint() {
            const k = this.nextKey;
            if (!k) return '';
            return (k === ' ' ? 'স্পেস' : k.toUpperCase()) + ' — ' + (FINGERS[k] || '');
        },
        get progress() {
            const n = this.isEn ? this.pos : Array.from(this.typed).length;
            return Math.min(100, Math.round(n / this.chars.length * 100));
        },
        cls(i) {
            if (this.isEn) {
                if (i < this.pos) return 'ok';
                if (i === this.pos) return 'cur' + (this.flash ? ' wrong' : '');
                return 'pending';
            }
            const t = Array.from(this.typed);
            if (i < t.length) return t[i] === this.chars[i] ? 'ok' : 'bad';
            return i === t.length ? 'cur' : 'pending';
        },
        onEnInput(e) {
            const val = e.target.value; e.target.value = '';
            for (const ch of Array.from(val)) {
                if (this.done) break;
                if (ch === this.expected) { this.hits++; this.pos++; this.flash = false; if (this.pos >= this.chars.length) return this.finish(); }
                else { this.mistakes++; const k = this.expected.trim() ? this.expected.toUpperCase() : 'স্পেস'; this.wrong[k] = (this.wrong[k] || 0) + 1; this.flash = true; setTimeout(() => { this.flash = false; }, 280); }
            }
        },
        finish() {
            if (this.isEn) {
                const total = this.hits + this.mistakes;
                this.accuracy = total ? Math.round(this.hits / total * 100) : 100;
                this.weak = Object.entries(this.wrong).sort((a, b) => b[1] - a[1]).slice(0, 4).map(x => x[0]);
            }
            this.done = true;
            try {
                const k = 'tm_lessons_done', a = JSON.parse(localStorage.getItem(k) || '[]');
                if (!a.includes(this.id)) { a.push(this.id); localStorage.setItem(k, JSON.stringify(a)); }
            } catch (e) {}
        },
        restart() {
            this.pos = 0; this.typed = ''; this.mistakes = 0; this.hits = 0; this.wrong = {}; this.flash = false; this.done = false; this.weak = [];
            this.$nextTick(() => this.focus());
        },
        // বাংলা: টাইপ করা অক্ষর সংখ্যা লক্ষ্যের সমান হলে শেষ
        watchBn() {
            const t = Array.from(this.typed);
            if (t.length >= this.chars.length && !this.done) {
                let ok = 0; this.chars.forEach((c, i) => { if (t[i] === c) ok++; });
                this.accuracy = Math.round(ok / this.chars.length * 100);
                this.mistakes = this.chars.length - ok;
                this.finish();
            }
        },
    };
}
</script>
@endsection
