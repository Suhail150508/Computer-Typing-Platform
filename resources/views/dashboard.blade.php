@extends('layouts.app')
@section('title', 'টাইপিং ল্যাব')

@push('head')
    <link rel="stylesheet" href="{{ asset('css/pages.css') }}?v={{ @filemtime(public_path('css/pages.css')) }}">
@endpush

@section('content')
    @php
        $hour = (int) now('Asia/Dhaka')->format('G');
        $greet = $hour < 5 ? 'শুভ রাত্রি' : ($hour < 12 ? 'শুভ সকাল' : ($hour < 16 ? 'শুভ দুপুর' : ($hour < 19 ? 'শুভ বিকেল' : 'শুভ সন্ধ্যা')));
        $passRate = $stats['attempts'] > 0 ? (int) round($stats['passed'] / $stats['attempts'] * 100) : 0;
        $canPlay = auth()->user()->canPractice();
    @endphp
    <div class="pg-wrap">
        {{-- হিরো --}}
        <section class="pg-hero anim-fade-up">
            <span class="pg-orb o1"></span><span class="pg-orb o2"></span>
            <div class="pg-keycaps" aria-hidden="true">
                <span class="pg-keycap k1">A</span><span class="pg-keycap k2">অ</span><span class="pg-keycap k3">WPM</span><span class="pg-keycap k4">ক</span>
            </div>

            <span class="pg-greet"><i></i>{{ $greet }}</span>
            <h1>স্বাগতম, <span class="pg-grad-text">{{ auth()->user()->name }}</span> <span style="display:inline-block;animation:pgRocket 2.4s ease-in-out infinite">👋</span></h1>
            <p class="pg-type-line" data-typewriter="প্রতিদিন ১৫ মিনিট অনুশীলনই আপনাকে দ্রুত টাইপিস্ট বানাবে।|বাংলা ও English — দুই ভাষাতেই নিজের স্পিড যাচাই করুন।|একটি ল্যাব বেছে নিন আর টাইপিং শুরু করুন!|ধৈর্য ধরুন, প্রতিদিন একটু একটু করে এগিয়ে যান।"><span class="pg-tw">একটি ল্যাব বেছে নিন আর টাইপিং শুরু করুন!</span><span class="pg-cursor"></span></p>

            <div class="pg-cta">
                <a href="{{ route('practice.index') }}" class="pg-btn pg-btn-white">🎯 টাইপিং শুরু <span class="arr">→</span></a>
                <a href="{{ route('free.index') }}" class="pg-btn pg-btn-glass">📄 শীট দেখে টাইপ</a>
            </div>
            <div style="margin-top:.7rem;display:flex;flex-wrap:wrap;gap:.65rem;align-items:center">@include('partials.typing-guide', ['variant' => 'glass'])@include('partials.exam-info')</div>
            @include('partials.level-picker')

            <!-- <div class="pg-stats">
                @foreach ([
                    ['মোট টেস্ট', $stats['attempts'], 0, '📄', ''],
                    ['পাস করেছেন', $stats['passed'], 0, '✅', ''],
                    ['সেরা WPM', $stats['best_wpm'], 1, '🚀', ''],
                    ['পাসের হার', $passRate, 0, '🏅', '%'],
                ] as $i => [$label, $value, $decimals, $icon, $suffix])
                    <div class="pg-stat anim-fade-up" style="animation-delay: {{ 120 + $i * 90 }}ms">
                        <div class="ico">{{ $icon }}</div>
                        <div class="num"><span class="count-up" data-target="{{ $value }}" data-decimals="{{ $decimals }}">0</span>{{ $suffix }}</div>
                        <div class="lbl">{{ $label }}</div>
                        @if ($suffix === '%')
                            <div class="pg-ring" style="--p: {{ $passRate }}"><svg viewBox="0 0 36 36"><circle class="bg" cx="18" cy="18" r="15.9155" pathLength="100"/><circle class="fg" cx="18" cy="18" r="15.9155" pathLength="100"/></svg></div>
                        @endif
                    </div>
                @endforeach
            </div> -->
        </section>

        {{-- কুইক অ্যাকশন --}}
        <div class="pg-tiles">
            <a href="{{ route('practice.index') }}" class="pg-tile is-main pg-tilt pg-reveal">
                <span class="t-ico">🎯</span>
                <div>
                    <h3>টাইপিং শুরু — বেসিক থেকে এক্সপার্ট</h3>
                    <p>ধাপে ধাপে কার্ড পাস করে বেসিক → অ্যাডভান্স → হার্ড আনলক করুন।</p>
                </div>
                <span class="go">শুরু করুন →</span>
            </a>
            <a href="{{ route('free.index') }}" class="pg-tile pg-tilt pg-reveal" style="--d: 80ms; --soft: #fef3c7">
                <span class="t-ico">📄</span><h3>শীট দেখে টাইপ</h3><p>বাংলা ও English প্র্যাকটিস</p>
            </a>
            <a href="{{ route('wpm.index') }}" class="pg-tile pg-tilt pg-reveal" style="--d: 140ms; --soft: #e0f2fe">
                <span class="t-ico">📈</span><h3>WPM Viewer</h3><p>স্পিডের গ্রাফ দেখুন</p>
            </a>
            <a href="{{ route('learn.index') }}" class="pg-tile pg-tilt pg-reveal" style="--d: 260ms; --soft: #d1fae5">
                <span class="t-ico">✨</span><h3>অনুশীলন</h3><p>টাইমার ছাড়া, নতুনদের জন্য</p>
            </a>
            <a href="{{ route('tips.index') }}" class="pg-tile pg-tilt pg-reveal" style="--d: 200ms; --soft: #fce7f3">
                <span class="t-ico">💡</span><h3>স্পিড টিপস</h3><p>দ্রুত টাইপিস্ট হওয়ার উপায়</p>
            </a>
        </div>

        @include('partials.subscription-card')

        {{-- যেখান থেকে ছেড়েছিলেন --}}
        @if ($last)
            @php
                $lastName = $last->isPractice() ? ($last->practiceCard?->title ?? 'টাইপিং শুরু') : ($last->lab?->name_bn ?? 'টাইপিং ল্যাব');
                $lastUrl = $last->isPractice()
                    ? route('practice.index', ['lang' => $last->language === 'english' ? 'english' : 'bangla', 'tier' => $last->practiceCard?->tier ?? 'basic'])
                    : ($last->lab ? route('typing.setup', [$last->lab, 'lang' => $last->language === 'english' ? 'english' : 'bangla']) : route('dashboard'));
            @endphp
            <div class="pg-resume pg-reveal">
                <span class="r-ico">▶</span>
                <div class="r-body">
                    <small>যেখান থেকে ছেড়েছিলেন · {{ $last->created_at->diffForHumans() }}</small>
                    <b>{{ $lastName }} — {{ \App\Support\Bn::num($last->wpm, 1) }} WPM ·
                        <span class="{{ $last->result === 'pass' ? 'pg-pass' : 'pg-fail' }}">{{ $last->result === 'pass' ? 'পাস' : 'ফেল' }}</span></b>
                </div>
                @if ($canPlay)
                    <a href="{{ $lastUrl }}" class="pg-btn" style="background:#4f46e5;color:#fff;box-shadow:0 12px 20px -10px #4f46e5">আবার চালিয়ে যান <span class="arr">→</span></a>
                @endif
            </div>
        @endif

        {{-- ল্যাব কার্ড --}}
        <div class="pg-sec pg-reveal">
            <h2>🧪 টাইপিং ল্যাব</h2>
            <p>ল্যাব বেছে ভাষা নির্বাচন করুন</p>
        </div>
        @if ($labs->isEmpty())
            <div class="pg-empty">
                এখনো কোনো ল্যাব যোগ করা হয়নি। <code>php artisan db:seed</code> চালান।
            </div>
        @else
            <div class="pg-labs">
                @foreach ($labs as $lab)
                    <div class="pg-lab pg-tilt pg-reveal" style="--d: {{ min($loop->index, 8) * 70 }}ms">
                        <div class="l-top">
                            <span class="l-badge">{{ $lab->badge_text ?? $lab->name_bn }}</span>
                            <h3>{{ $lab->name_bn }}</h3>
                        </div>
                        <div class="l-body">
                            <div class="l-meter">
                                <span>⏱️ ন্যূনতম {{ \App\Support\Bn::digits($lab->min_wpm) }} WPM</span>
                                <span class="bar"><i style="--w: {{ min(100, (int) round($lab->min_wpm / 60 * 100)) }}%"></i></span>
                            </div>
                            <p class="l-sub">{{ $lab->subtitle }}</p>
                            <div class="l-pills">
                                <span class="pg-pill">🇧🇩 বাংলা {{ \App\Support\Bn::digits($lab->bangla_count) }} টি</span>
                                <span class="pg-pill">🇬🇧 English {{ $lab->english_count }}</span>
                            </div>
                            <div class="l-btns">
                                <a href="{{ route('typing.setup', [$lab, 'lang' => 'bangla']) }}" class="pg-lang bn {{ $lab->bangla_count ? '' : 'disabled' }}">বাংলা ▸</a>
                                <a href="{{ route('typing.setup', [$lab, 'lang' => 'english']) }}" class="pg-lang en {{ $lab->english_count ? '' : 'disabled' }}">English ▸</a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script defer src="{{ asset('js/pages.js') }}?v={{ @filemtime(public_path('js/pages.js')) }}"></script>
@endpush
