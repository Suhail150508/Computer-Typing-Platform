@extends('layouts.app')
@section('title', 'WPM Viewer')

@section('content')
@php
    use App\Support\Bn;
    $hasData = $stats['total'] > 0;
@endphp
<div class="max-w-6xl mx-auto" x-data="{ lang: '{{ $language }}' }">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-6 anim-fade-up">
        <div>
            <h1 class="text-2xl sm:text-3xl font-bold">📈 WPM Viewer</h1>
            <p class="text-slate-500 text-sm mt-1">সময়ের সাথে আপনার টাইপিং স্পিড কীভাবে বাড়ছে, তার গ্রাফ ও পরিসংখ্যান।</p>
        </div>
        <div class="inline-flex bg-white border border-slate-200 rounded-full p-1 shadow-sm">
            <a href="{{ route('wpm.index', ['lang' => 'bangla']) }}" class="px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $language === 'bangla' ? 'bg-emerald-500 text-white shadow' : 'text-slate-500 hover:text-slate-700' }}">🇧🇩 বাংলা</a>
            <a href="{{ route('wpm.index', ['lang' => 'english']) }}" class="px-4 py-1.5 rounded-full text-sm font-semibold transition {{ $language === 'english' ? 'bg-sky-500 text-white shadow' : 'text-slate-500 hover:text-slate-700' }}">🇬🇧 English</a>
        </div>
    </div>

    @if (! $hasData)
        <div class="rounded-3xl bg-white border-2 border-dashed border-slate-200 p-10 sm:p-14 text-center anim-pop">
            <div class="text-5xl mb-3">📊</div>
            <h2 class="font-bold text-lg text-slate-700">এখনো কোনো ডেটা নেই</h2>
            <p class="text-slate-500 text-sm mt-1 max-w-sm mx-auto">{{ $language === 'bangla' ? 'বাংলা' : 'English' }} ভাষায় অন্তত একটি টেস্ট দিলেই এখানে আপনার স্পিডের গ্রাফ দেখতে পাবেন।</p>
            <a href="{{ route('practice.index', ['lang' => $language]) }}" class="inline-flex items-center gap-2 mt-5 bg-brand-600 hover:bg-brand-700 text-white font-semibold rounded-full px-6 py-2.5 transition">🎯 এখনই টেস্ট দিন</a>
        </div>
    @else
        {{-- স্ট্যাট কার্ড --}}
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 mb-6">
            @foreach ([
                ['label' => 'সেরা WPM', 'value' => $stats['best_wpm'], 'decimals' => 1, 'icon' => '🚀', 'grad' => 'from-blue-50 to-white', 'color' => 'text-blue-700'],
                ['label' => 'সেরা Accuracy', 'value' => $stats['best_accuracy'], 'decimals' => 1, 'suffix' => '%', 'icon' => '🎯', 'grad' => 'from-emerald-50 to-white', 'color' => 'text-emerald-700'],
                ['label' => 'গড় WPM (সাম্প্রতিক ১০)', 'value' => $stats['avg_wpm_recent'], 'decimals' => 1, 'icon' => '📐', 'grad' => 'from-violet-50 to-white', 'color' => 'text-violet-700'],
                ['label' => 'মোট টেস্ট', 'value' => $stats['total'], 'decimals' => 0, 'icon' => '📄', 'grad' => 'from-slate-50 to-white', 'color' => 'text-slate-700'],
                ['label' => 'পাস করেছেন', 'value' => $stats['passed'], 'decimals' => 0, 'icon' => '✅', 'grad' => 'from-lime-50 to-white', 'color' => 'text-lime-700'],
                ['label' => 'ধারাবাহিক দিন', 'value' => $stats['streak'], 'decimals' => 0, 'suffix' => ' দিন', 'icon' => '🔥', 'grad' => 'from-orange-50 to-white', 'color' => 'text-orange-700'],
            ] as $i => $s)
                <div class="anim-fade-up rounded-2xl bg-gradient-to-br {{ $s['grad'] }} border border-slate-200 p-3 sm:p-4 hover:shadow-md transition" style="animation-delay: {{ $i * 60 }}ms">
                    <div class="text-lg sm:text-xl">{{ $s['icon'] }}</div>
                    <div class="text-lg sm:text-2xl font-bold mt-1 {{ $s['color'] }}">
                        <span class="count-up" data-target="{{ $s['value'] }}" data-decimals="{{ $s['decimals'] }}">0</span>{{ $s['suffix'] ?? '' }}
                    </div>
                    <div class="text-[10px] sm:text-xs text-slate-500 leading-tight mt-0.5">{{ $s['label'] }}</div>
                </div>
            @endforeach
        </div>

        {{-- ট্রেন্ড চার্ট --}}
        <div class="rounded-3xl bg-white border border-slate-200 shadow-sm p-4 sm:p-6 mb-6 anim-fade-up">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-slate-700">স্পিডের উন্নতির গ্রাফ <span class="text-slate-400 font-normal text-sm">(সর্বশেষ {{ Bn::digits($series->count()) }}টি টেস্ট)</span></h2>
                <div class="flex items-center gap-3 text-xs text-slate-500">
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-blue-500"></span> WPM</span>
                    <span class="flex items-center gap-1"><span class="w-2.5 h-2.5 rounded-full bg-emerald-400"></span> Accuracy %</span>
                </div>
            </div>
            <div class="relative h-56 sm:h-72">
                <canvas id="wpmChart"></canvas>
            </div>
        </div>

        {{-- সাম্প্রতিক টেস্টসমূহ --}}
        <div class="rounded-3xl bg-white border border-slate-200 shadow-sm overflow-hidden anim-fade-up">
            <h2 class="font-bold text-slate-700 px-4 sm:px-6 pt-5 pb-3">সাম্প্রতিক টেস্টসমূহ</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-center whitespace-nowrap">
                    <thead class="bg-slate-50 text-slate-500 text-xs uppercase">
                        <tr>
                            <th class="px-3 py-2.5 text-left">তারিখ</th>
                            <th class="px-3 py-2.5 text-left">উৎস</th>
                            <th class="px-3 py-2.5">WPM</th>
                            <th class="px-3 py-2.5">Accuracy</th>
                            <th class="px-3 py-2.5">ফলাফল</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @foreach ($recent as $a)
                            <tr class="hover:bg-blue-50/40 transition cursor-pointer" onclick="window.location='{{ route('results.show', $a) }}'">
                                <td class="px-3 py-2.5 text-left text-slate-500">{{ $a->created_at->format('d M, h:i A') }}</td>
                                <td class="px-3 py-2.5 text-left">{{ $a->sourceLabel() }}</td>
                                <td class="px-3 py-2.5 font-semibold">{{ Bn::num($a->wpm) }}</td>
                                <td class="px-3 py-2.5">{{ Bn::num($a->accuracy) }}%</td>
                                <td class="px-3 py-2.5">
                                    <span class="inline-block min-w-[3.4rem] rounded-full text-white text-xs font-bold px-2.5 py-1 {{ $a->isPass() ? 'bg-green-600' : 'bg-red-600' }}">{{ $a->isPass() ? 'PASS' : 'FAIL' }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 sm:px-6 py-3 text-center border-t border-slate-100">
                <a href="{{ route('results.index') }}" class="text-brand-600 text-sm font-semibold hover:underline">সব রেজাল্ট দেখুন →</a>
            </div>
        </div>
    @endif
</div>

@if ($hasData)
@push('scripts')
<script src="{{ asset('js/chart.umd.min.js') }}?v={{ @filemtime(public_path('js/chart.umd.min.js')) }}"></script>
<script>
    var labels = @json($series->map(fn ($s) => $s->created_at->format('d M, h:i A')));
    var wpmData = @json($series->map(fn ($s) => (float) $s->wpm));
    var accData = @json($series->map(fn ($s) => (float) $s->accuracy));

    if (typeof Chart === 'undefined') {
        var box = document.getElementById('wpmChart').parentNode;
        box.innerHTML = '<div style="height:100%;display:grid;place-items:center;text-align:center;color:#64748b;font-size:.9rem">গ্রাফ লোড হয়নি। পেজটি রিফ্রেশ (Ctrl+F5) করে দেখুন।</div>';
    } else new Chart(document.getElementById('wpmChart').getContext('2d'), {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'WPM', data: wpmData, borderColor: '#3b82f6', backgroundColor: 'rgba(59,130,246,.12)',
                    tension: .35, fill: true, pointRadius: 3, pointBackgroundColor: '#3b82f6', yAxisID: 'y',
                },
                {
                    label: 'Accuracy %', data: accData, borderColor: '#34d399', backgroundColor: 'rgba(52,211,153,.08)',
                    tension: .35, fill: false, pointRadius: 2, borderDash: [5, 4], yAxisID: 'y1',
                },
            ],
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { maxRotation: 0, autoSkip: true, maxTicksLimit: 6 }, grid: { display: false } },
                y: { position: 'left', beginAtZero: true, title: { display: true, text: 'WPM' } },
                y1: { position: 'right', min: 0, max: 100, grid: { drawOnChartArea: false }, title: { display: true, text: 'Accuracy %' } },
            },
        },
    });
</script>
@endpush
@endif
@endsection
