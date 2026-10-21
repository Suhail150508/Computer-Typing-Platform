@extends('layouts.app')
@section('title', 'অনুশীলন')

@push('head')
<link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ @filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
<div class="pg-wrap" x-data="learnList()">
    <section class="ln-hero2 anim-fade-up">
        <span class="ln-fl f1">A</span><span class="ln-fl f2">ক</span><span class="ln-fl f3">J</span><span class="ln-fl f4">অ</span>
        <div class="ln-hero2-in">
            <h1>✨ অনুশীলন <small>চাপ ছাড়া শিখুন</small></h1>
            <p>কোনো টাইমার নেই, পাস-ফেল নেই। নিজের গতিতে ধাপে ধাপে শিখুন কোন আঙুল দিয়ে কোন কী চাপতে হয়।</p>
            <div class="ln-prog">
                <div class="ln-prog-t"><span>আপনার অগ্রগতি</span><b x-text="bn(doneCount) + ' / ' + bn(mine.length) + ' পাঠ সম্পন্ন'"></b></div>
                <div class="ln-prog-bar"><i :style="'width:' + (mine.length ? Math.round(doneCount / mine.length * 100) : 0) + '%'"></i></div>
            </div>
            <div style="margin-top:1rem;display:flex;flex-wrap:wrap;gap:.65rem;align-items:center">@include('partials.typing-guide', ['variant' => 'glass'])@include('partials.exam-info')</div>
        </div>
    </section>

    <div class="ln-tabs anim-fade-up">
        <a href="{{ route('learn.index', ['lang' => 'english']) }}" class="ln-tab t-en {{ $lang === 'english' ? 'on' : '' }}">
            <span class="ti">Aa</span><span class="tx"><b>English</b><small>ইংরেজি টাইপিং</small></span>
        </a>
        <a href="{{ route('learn.index', ['lang' => 'bijoy']) }}" class="ln-tab t-bj {{ $lang === 'bijoy' ? 'on' : '' }}">
            <span class="ti">ক</span><span class="tx"><b>বাংলা · বিজয়</b><small>কী-তে বাংলা অক্ষর</small></span>
        </a>
        <a href="{{ route('learn.index', ['lang' => 'avro']) }}" class="ln-tab t-av {{ $lang === 'avro' ? 'on' : '' }}">
            <span class="ti">অ</span><span class="tx"><b>বাংলা · অভ্র</b><small>ami → আমি</small></span>
        </a>
    </div>

    @if ($lang === 'bijoy')
        <p class="ln-infobox">⌨️ <b>বিজয় লেআউট:</b> কীবোর্ডের ছবিতে প্রতিটি কী-তে কোন বাংলা অক্ষর আছে দেখে দেখে শিখুন। পরের কী জ্বলবে, আঙুলের নামও বলে দেওয়া হবে। (কম্পিউটারের কীবোর্ডে অনুশীলন করুন; আলাদা বিজয় সফটওয়্যার লাগবে না।)</p>
    @elseif ($lang === 'avro')
        <p class="ln-infobox">⌨️ <b>অভ্র ফোনেটিক:</b> ইংরেজি অক্ষরে বাংলা উচ্চারণ লিখে বাংলা পাওয়া যায় (ami → আমি)। কোন অক্ষরে কী আসে নিচের রুলস চার্টে দেখুন।</p>
        @include('learn.avro-chart')
    @endif

    <div class="ln-grid">
        @foreach ($lessons as $l)
            <a href="{{ route('learn.show', $l['id']) }}" class="ln-card anim-fade-up" style="animation-delay: {{ $loop->index * 70 }}ms" :class="done.includes('{{ $l['id'] }}') && 'is-done'">
                <span class="no">পাঠ {{ \App\Support\Bn::digits($l['no']) }}</span>
                <span class="tick" x-show="done.includes('{{ $l['id'] }}')" x-cloak>✓</span>
                <div class="ic">{{ $l['icon'] }}</div>
                <h3>{{ $l['title'] }}</h3>
                <p>{{ $l['desc'] }}</p>
                <span class="go"><span x-text="done.includes('{{ $l['id'] }}') ? 'আবার করুন' : 'শুরু করুন'">শুরু করুন</span> →</span>
            </a>
        @endforeach
    </div>

</div>

<script>
function learnList() {
    return {
        mine: @js(array_column($lessons, 'id')),
        done: (() => { let a = @js($doneDb); try { a = a.concat(JSON.parse(localStorage.getItem('tm_lessons_done') || '[]')); } catch (e) {} return a; })(),
        get doneCount() { return this.mine.filter(x => this.done.includes(x)).length; },
        bn(n) { return String(n).replace(/[0-9]/g, d => '০১২৩৪৫৬৭৮৯'[d]); },
    };
}
</script>
@endsection
