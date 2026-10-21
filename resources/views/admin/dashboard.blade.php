@extends('admin.layouts.app')
@section('title', 'অ্যাডমিন ড্যাশবোর্ড')

@section('content')
<div class="max-w-7xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-800 mb-5">ড্যাশবোর্ড</h1>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        @foreach ([
            ['মোট ইউজার', number_format($stats['total_users']), '👥', 'from-indigo-50'],
            ['সক্রিয় সাবস্ক্রাইবার', number_format($stats['active_subscribers']), '✅', 'from-emerald-50'],
            ['গত ৭ দিনে নতুন', number_format($stats['new_users_7d']), '🆕', 'from-sky-50'],
            ['মোট টাইপিং টেস্ট', number_format($stats['total_attempts']), '⌨️', 'from-amber-50'],
            ['আজকের টেস্ট', number_format($stats['attempts_today']), '📈', 'from-rose-50'],
            ['পাস রেট', $stats['pass_rate'].'%', '🎯', 'from-lime-50'],
            ['ওয়ালেট ব্যালেন্স (মোট)', number_format($stats['wallet_total'], 0).' ৳', '👛', 'from-fuchsia-50'],
            ['৩০ দিনে আয়', number_format($stats['revenue_30d'], 0).' ৳', '💰', 'from-cyan-50'],
        ] as [$label, $value, $icon, $grad])
            <div class="rounded-2xl bg-gradient-to-br {{ $grad }} to-white border border-slate-200 p-4">
                <div class="text-xl">{{ $icon }}</div>
                <div class="text-xl font-bold mt-1 text-slate-800">{{ $value }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ $label }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 sm:gap-4 mb-6">
        @foreach ([
            ['এখন অনলাইন (গত ৫ মিনিট)', $stats['online_now'], '🟢', route('admin.activity.index'), 'border-emerald-200 bg-emerald-50'],
            ['ইউজারের নতুন মেসেজ', $stats['new_feedback'], '📨', route('admin.feedback.index'), 'border-indigo-200 bg-indigo-50'],
            ['পেমেন্ট যাচাইয়ের অপেক্ষায়', $stats['pending_payments'], '⏳', route('admin.payments.index'), 'border-amber-200 bg-amber-50'],
            ['শীঘ্রই মেয়াদ শেষ', $stats['expiring_soon'], '🔔', route('admin.users.index', ['filter' => 'expiring']), 'border-orange-200 bg-orange-50'],
            ['মেয়াদ শেষ ইউজার', $stats['expired_users'], '⏰', route('admin.users.index', ['filter' => 'expired']), 'border-rose-200 bg-rose-50'],
            ['বন্ধ (নিষ্ক্রিয়) একাউন্ট', $stats['inactive_users'], '🚫', route('admin.users.index', ['filter' => 'inactive']), 'border-slate-200 bg-slate-50'],
        ] as [$label, $value, $icon, $url, $cls])
            <a href="{{ $url }}" class="rounded-2xl border {{ $cls }} p-4 hover:-translate-y-0.5 hover:shadow-md transition">
                <div class="text-xl">{{ $icon }}</div>
                <div class="text-xl font-bold mt-1 text-slate-800">{{ number_format($value) }}</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ $label }} →</div>
            </a>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-2 gap-5">
        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5">
            <h2 class="font-bold text-slate-700 mb-3">গত ৭ দিনের প্রবণতা</h2>
            {{-- বাইরের লাইব্রেরি (Chart.js/CDN) ছাড়াই সার্ভারে আঁকা SVG চার্ট — তাই ইন্টারনেট/CDN সমস্যায়ও দেখা যায় --}}
            @php
                $w = 560; $h = 220; $pl = 34; $pr = 12; $pt = 14; $pb = 30;
                $series = [
                    ['নতুন ইউজার', collect($signupSeries)->values()->all(), '#6366f1'],
                    ['টাইপিং টেস্ট', collect($attemptSeries)->values()->all(), '#f59e0b'],
                ];
                $labelList = collect($labels)->values()->all();
                $max = max(1, ...collect($series)->flatMap(fn ($x) => $x[1])->all());
                $step = $max <= 4 ? 1 : (int) ceil($max / 4);
                $top = $step * (int) ceil($max / $step);
                $n = max(1, count($labelList) - 1);
                $x = fn ($i) => $pl + ($w - $pl - $pr) * ($i / $n);
                $y = fn ($v) => $pt + ($h - $pt - $pb) * (1 - $v / $top);
            @endphp
            <svg viewBox="0 0 {{ $w }} {{ $h }}" class="w-full h-auto" role="img" aria-label="গত ৭ দিনের নতুন ইউজার ও টাইপিং টেস্টের গ্রাফ">
                @for ($g = 0; $g <= $top; $g += $step)
                    <line x1="{{ $pl }}" x2="{{ $w - $pr }}" y1="{{ $y($g) }}" y2="{{ $y($g) }}" stroke="#e2e8f0" stroke-width="1"/>
                    <text x="{{ $pl - 6 }}" y="{{ $y($g) + 4 }}" text-anchor="end" font-size="11" fill="#94a3b8">{{ $g }}</text>
                @endfor
                @foreach ($labelList as $i => $lb)
                    <text x="{{ $x($i) }}" y="{{ $h - 8 }}" text-anchor="middle" font-size="11" fill="#94a3b8">{{ $lb }}</text>
                @endforeach
                @foreach ($series as [$name, $vals, $color])
                    @php
                        $pts = collect($vals)->map(fn ($v, $i) => round($x($i), 1).','.round($y($v), 1))->implode(' ');
                        $area = round($x(0), 1).','.round($y(0), 1).' '.$pts.' '.round($x(count($vals) - 1), 1).','.round($y(0), 1);
                    @endphp
                    <polygon points="{{ $area }}" fill="{{ $color }}" opacity=".08"/>
                    <polyline points="{{ $pts }}" fill="none" stroke="{{ $color }}" stroke-width="2.5" stroke-linejoin="round" stroke-linecap="round"/>
                    @foreach ($vals as $i => $v)
                        <circle cx="{{ round($x($i), 1) }}" cy="{{ round($y($v), 1) }}" r="3.5" fill="#fff" stroke="{{ $color }}" stroke-width="2"><title>{{ $labelList[$i] }}: {{ $name }} {{ $v }}</title></circle>
                    @endforeach
                @endforeach
            </svg>
            <div class="flex justify-center gap-5 mt-2 text-xs text-slate-500">
                @foreach ($series as [$name, , $color])
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full" style="background: {{ $color }}"></span>{{ $name }}</span>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5">
            <h2 class="font-bold text-slate-700 mb-3">নতুন ইউজার</h2>
            <div class="divide-y divide-slate-100">
                @forelse ($recentUsers as $u)
                    <a href="{{ route('admin.users.show', $u) }}" class="flex items-center gap-3 py-2.5 hover:bg-slate-50 -mx-2 px-2 rounded-lg transition">
                        @include('partials.avatar', ['user' => $u, 'size' => 'w-9 h-9', 'text' => 'text-sm'])
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-slate-700 truncate">{{ $u->name }}</p>
                            <p class="text-xs text-slate-400 truncate">{{ $u->email }}</p>
                        </div>
                        <span class="text-xs text-slate-400">{{ $u->created_at->diffForHumans() }}</span>
                    </a>
                @empty
                    <p class="text-sm text-slate-400 py-4">কোনো ইউজার নেই।</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 p-4 sm:p-5 mt-5">
        <div class="flex items-center justify-between mb-3">
            <h2 class="font-bold text-slate-700">সাম্প্রতিক টাইপিং অ্যাটেম্পট</h2>
            <a href="{{ route('admin.attempts.index') }}" class="text-sm text-indigo-600 font-semibold hover:underline">সব দেখুন →</a>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm whitespace-nowrap">
                <thead class="text-xs text-slate-400 uppercase border-b border-slate-100">
                    <tr><th class="text-left py-2">ইউজার</th><th class="text-left py-2">উৎস</th><th class="py-2">WPM</th><th class="py-2">Accuracy</th><th class="py-2">ফলাফল</th><th class="text-right py-2">সময়</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-50">
                    @forelse ($recentAttempts as $a)
                        <tr>
                            <td class="py-2.5">{{ $a->user?->name ?? '—' }}</td>
                            <td class="py-2.5 text-slate-500">{{ $a->sourceLabel() }}</td>
                            <td class="py-2.5 text-center">{{ \App\Support\Bn::num($a->wpm) }}</td>
                            <td class="py-2.5 text-center">{{ \App\Support\Bn::num($a->accuracy) }}%</td>
                            <td class="py-2.5 text-center"><span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $a->isPass() ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ strtoupper($a->result) }}</span></td>
                            <td class="py-2.5 text-right text-slate-400 text-xs">{{ $a->created_at->diffForHumans() }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-center text-slate-400 py-6">কোনো অ্যাটেম্পট নেই।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
