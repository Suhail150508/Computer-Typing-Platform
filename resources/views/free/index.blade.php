@extends('layouts.app')
@section('title', 'শীট/পেপার দেখে টাইপ')

@section('content')
@php $canPlay = auth()->user()->canPractice(); $lab = \App\Models\Lab::orderBy('id')->first(); @endphp
<div class="max-w-4xl mx-auto" x-data="freeTyping()" x-init="init()">
    <div class="mb-4 anim-fade-up">
        <h1 class="text-2xl sm:text-3xl font-bold">⌨️ শীট/পেপার দেখে টাইপ প্র্যাকটিস</h1>
        <p class="text-slate-500 text-sm mt-1 leading-relaxed">কম্পিউটার ব্যবহারিক পরীক্ষার মতো পরিবেশ — নিজের হাতের শীট/বই দেখে বা পেস্ট করা লেখা দেখে নিচের বক্সে টাইপ করুন। (এই মোডের ফলাফল শুধু আপনার প্র্যাকটিসের জন্য, সেভ হয় না।)</p>
    </div>

    @unless ($canPlay)
        <div class="mb-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-700 text-sm px-4 py-3">
            আপনার মেয়াদ শেষ — <a href="{{ route('premium.index') }}" class="font-bold underline">একাউন্ট সক্রিয় করুন</a>।
        </div>
    @endunless

    {{-- মোড ট্যাব --}}
    <div class="flex flex-wrap gap-2 mb-4">
        <template x-for="m in modes" :key="m.key">
            <button type="button" @click="setMode(m.key)" :disabled="running"
                    class="px-4 py-2 rounded-full border text-sm font-semibold transition"
                    :class="mode === m.key ? 'bg-brand-600 text-white border-brand-600 shadow' : 'bg-white text-slate-600 border-slate-200 hover:border-brand-400'"
                    x-text="m.label"></button>
        </template>
        @if ($lab)
            <a href="{{ route('typing.setup', $lab) }}" class="px-4 py-2 rounded-full border text-sm font-semibold bg-white text-slate-600 border-slate-200 hover:border-brand-400">Passage দেখে টাইপ ↗</a>
        @endif
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-4 mb-4">
        <span class="block text-xs font-semibold text-slate-500 mb-1">ভাষা (Language)</span>
        <div class="inline-flex bg-slate-100 rounded-full p-1">
            <button type="button" @click="setLang('bangla')" :disabled="running" class="px-5 py-1.5 rounded-full text-sm font-semibold transition" :class="lang === 'bangla' ? 'bg-white shadow text-brand-700' : 'text-slate-500'">🇧🇩 বাংলা</button>
            <button type="button" @click="setLang('english')" :disabled="running" class="px-5 py-1.5 rounded-full text-sm font-semibold transition" :class="lang === 'english' ? 'bg-white shadow text-brand-700' : 'text-slate-500'">🇬🇧 English</button>
        </div>
        <p class="text-xs text-slate-500 mt-2" x-text="lang === 'english' ? 'English টাইপ করতে কীবোর্ড লেআউট English (US) রাখুন — বদলাতে Windows-এ Alt+Shift বা Win+Space চাপুন।' : 'বাংলা টাইপ করতে আপনার বাংলা কীবোর্ড/সফটওয়্যার (বিজয়, অভ্র ইত্যাদি) চালু রাখুন।'"></p>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 p-4 mb-4 grid sm:grid-cols-2 gap-4">
        <div>
            <span class="block text-xs font-semibold text-slate-500 mb-1">সময়</span>
            <div class="flex flex-wrap gap-1.5">
                <template x-for="d in [1,2,3,5,10,15]" :key="d">
                    <button type="button" @click="duration = d" :disabled="running" class="px-3 py-1 rounded-lg border text-sm font-semibold"
                            :class="duration === d ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-slate-600 border-slate-200'" x-text="bn(d) + ' মি.'"></button>
                </template>
            </div>
        </div>
        <p class="text-xs text-slate-500 leading-relaxed self-center" x-text="modeHelp()"></p>
    </div>

    {{-- পেস্ট মোড: রেফারেন্স লেখা --}}
    <div x-show="mode === 'paste'" x-cloak class="mb-4">
        <label class="block text-sm font-semibold text-slate-600 mb-1">যে লেখাটি দেখে টাইপ করবেন সেটি এখানে পেস্ট করুন:</label>
        <textarea x-model="reference" :readonly="running" :lang="langCode()" rows="5" class="field w-full" :placeholder="lang === 'english' ? 'Paste the text here...' : 'এখানে লেখা পেস্ট করুন...'"></textarea>
        <div x-show="running || finished" class="mt-2 rounded-xl bg-amber-50 border border-amber-200 p-3 text-slate-700 leading-relaxed" x-text="reference"></div>
    </div>

    {{-- টাইপিং এলাকা --}}
    <div class="rounded-2xl bg-white border border-slate-200 p-4 shadow-sm">
        <div class="flex flex-wrap items-center gap-2 mb-3">
            <span class="px-3 py-1 rounded-full bg-slate-100 text-sm">সময়: <b x-text="clock()"></b></span>
            <span class="px-3 py-1 rounded-full bg-slate-100 text-sm" x-show="mode !== 'hard' || finished">শব্দ: <b x-text="bn(words())"></b></span>
            <span class="px-3 py-1 rounded-full bg-slate-100 text-sm" x-show="mode !== 'hard' || finished">WPM: <b x-text="bn(wpm())"></b></span>
            <span class="px-3 py-1 rounded-full bg-violet-50 text-violet-700 text-sm" x-show="mode === 'hard' && !finished">🙈 স্পিড লুকানো আছে</span>
            <button type="button" @click="finish()" x-show="running" class="ml-auto px-4 py-1.5 rounded-xl bg-rose-600 text-white text-sm font-semibold">শেষ করুন</button>
            <button type="button" @click="reset()" x-show="finished" class="ml-auto px-4 py-1.5 rounded-xl bg-brand-600 text-white text-sm font-semibold">আবার শুরু করুন</button>
        </div>
        <textarea x-ref="box" x-model="text" :lang="langCode()" :dir="'ltr'" @input="onInput()" @paste.prevent @copy.prevent @keydown.ctrl.enter.prevent="finish()" @keydown.meta.enter.prevent="finish()"
                  :readonly="finished || !canStart()" rows="9" spellcheck="false" autocomplete="off"
                  class="w-full rounded-xl border-2 border-cyan-300 focus:border-cyan-500 outline-none p-4 text-lg leading-relaxed"
                  :placeholder="!canStart() ? (lang === 'english' ? 'Paste the text above first' : 'আগে উপরে লেখাটি পেস্ট করুন') : (lang === 'english' ? 'Start typing here — the timer starts with your first key...' : 'শীট দেখে এখানে টাইপ শুরু করুন — প্রথম অক্ষর লিখলেই টাইমার চালু হবে...')"></textarea>
        <p class="text-[11px] text-slate-400 mt-1">অথবা কীবোর্ড থেকে Ctrl+Enter চাপুন শেষ করতে।</p>
    </div>

    {{-- ফলাফল --}}
    <div x-show="finished" x-cloak class="mt-4 rounded-2xl bg-emerald-50 border border-emerald-200 p-5 anim-fade-up">
        <h2 class="font-bold text-lg mb-3">✅ আপনার ফলাফল</h2>
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-center">
            <div class="bg-white rounded-xl p-3"><div class="text-xs text-slate-500">সময়</div><div class="text-xl font-bold" x-text="clock()"></div></div>
            <div class="bg-white rounded-xl p-3"><div class="text-xs text-slate-500">শব্দ</div><div class="text-xl font-bold" x-text="bn(words())"></div></div>
            <div class="bg-white rounded-xl p-3"><div class="text-xs text-slate-500">WPM</div><div class="text-xl font-bold" x-text="bn(wpm())"></div></div>
            <div class="bg-white rounded-xl p-3"><div class="text-xs text-slate-500">নির্ভুলতা</div><div class="text-xl font-bold" :class="!hasRef() ? 'text-slate-300' : accuracy() >= 90 ? 'text-emerald-600' : (accuracy() >= 70 ? 'text-amber-600' : 'text-rose-600')" x-text="hasRef() ? bn(accuracy()) + '%' : '—'"></div></div>
        </div>
        <p x-show="hasRef()" class="text-sm text-slate-600 mt-3">সঠিক শব্দ: <b x-text="bn(correctWords())"></b> / মূল লেখায় <b x-text="bn(refWords().length)"></b> শব্দ · ভুল/বাদ/বাড়তি: <b x-text="bn(Math.max(refWords().length, typedWords().length) - correctWords())"></b></p>
        <div x-show="mode !== 'paste'" class="mt-4 rounded-xl bg-amber-50 border-2 border-amber-300 p-3">
            <label class="block text-sm font-semibold text-slate-700 mb-1">🎯 নির্ভুলতা দেখতে চান? শীটের মূল লেখাটি এখানে পেস্ট করুন:</label>
            <textarea x-model="reference" rows="4" class="field w-full bg-white" placeholder="যে শীট/বই দেখে টাইপ করেছেন তার লেখাটি এখানে পেস্ট করুন — সাথে সাথে নির্ভুলতা দেখাবে..."></textarea>
        </div>
    </div>
</div>

<script>
function freeTyping() {
    return {
        modes: [
            {key: 'easy', label: 'শীট দেখে টাইপ — মোড ১ (সহজ)'},
            {key: 'hard', label: 'শীট দেখে টাইপ — মোড ২ (কঠিন, বেশি কার্যকর)'},
            {key: 'paste', label: 'লেখা পেস্ট করে টাইপ'},
        ],
        mode: 'easy', lang: 'bangla', duration: 5, text: '', reference: '',
        running: false, finished: false, secs: 0, timer: null, t0: 0,
        init() {},
        bn(n) { return String(n).replace(/\d/g, d => '০১২৩৪৫৬৭৮৯'[d]); },
        setLang(l) { if (!this.running) { this.lang = l; this.reset(); } },
        langCode() { return this.lang === 'english' ? 'en' : 'bn'; },
        setMode(m) { if (!this.running) { this.mode = m; this.reset(); } },
        modeHelp() {
            return {
                easy: 'নিজের কাগজের শীট/বই দেখে টাইপ করুন। টাইপ করার সময় শব্দ ও WPM লাইভ দেখা যাবে।',
                hard: 'একই রকম, কিন্তু টাইপ করার সময় স্পিড লুকানো থাকবে — চাপ ও মনোযোগ বাড়ে, পরীক্ষার জন্য বেশি কার্যকর।',
                paste: 'উপরের বক্সে লেখা পেস্ট করুন, তারপর সেটি দেখে নিচে টাইপ করুন। শেষে নির্ভুলতাও দেখাবে।',
            }[this.mode];
        },
        canStart() { return this.mode !== 'paste' || this.reference.trim().length > 0; },
        words() { return this.text.trim() ? this.text.trim().split(/\s+/).length : 0; },
        wpm() { const m = Math.max(this.secs, 1) / 60; return Math.round((this.text.replace(/\s+$/,'').length / 5) / m); },
        refWords() { return this.reference.trim() ? this.reference.trim().split(/\s+/).map(w => w.normalize('NFC')) : []; },
        typedWords() { return this.text.trim() ? this.text.trim().split(/\s+/).map(w => w.normalize('NFC')) : []; },
        hasRef() { return this.refWords().length > 0; },
        // শব্দ-ভিত্তিক LCS: একটা শব্দ বাদ/বাড়তি হলে পরের সব শব্দ ভুল ধরা হয় না
        correctWords() {
            const r = this.refWords(), t = this.typedWords();
            if (!r.length || !t.length) return 0;
            let prev = new Array(t.length + 1).fill(0);
            for (let i = 1; i <= r.length; i++) {
                const cur = new Array(t.length + 1).fill(0);
                for (let j = 1; j <= t.length; j++) cur[j] = r[i-1] === t[j-1] ? prev[j-1] + 1 : Math.max(prev[j], cur[j-1]);
                prev = cur;
            }
            return prev[t.length];
        },
        accuracy() {
            const d = Math.max(this.refWords().length, this.typedWords().length);
            return d ? Math.round(this.correctWords() / d * 100) : 0;
        },
        clock() { const s = this.running || this.finished ? this.secs : this.duration * 60; const left = this.finished ? this.secs : s; const v = this.finished ? left : Math.max(this.duration * 60 - this.secs, 0); const x = this.running || this.finished ? (this.finished ? this.secs : v) : this.duration * 60; return this.bn(String(Math.floor(x / 60)).padStart(2, '0') + ':' + String(x % 60).padStart(2, '0')); },
        onInput() {
            if (this.running || this.finished || !this.text.length) return;
            this.running = true; this.t0 = Date.now();
            this.timer = setInterval(() => {
                this.secs = Math.floor((Date.now() - this.t0) / 1000);
                if (this.secs >= this.duration * 60) this.finish();
            }, 250);
        },
        finish() {
            if (!this.running) return;
            clearInterval(this.timer); this.secs = Math.min(Math.max(Math.floor((Date.now() - this.t0) / 1000), 1), this.duration * 60);
            this.running = false; this.finished = true;
        },
        reset() { clearInterval(this.timer); this.text = ''; if (this.mode !== 'paste') this.reference = ''; this.secs = 0; this.running = false; this.finished = false; },
    };
}
</script>
@endsection
