@extends('admin.layouts.app')
@section('title', 'অ্যাক্টিভিটি লগ')

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ auto: false, t: null, toggle() { this.auto = !this.auto; clearInterval(this.t); if (this.auto) this.t = setInterval(() => location.reload(), 30000); } }">
    <div class="flex items-center justify-between gap-3 mb-5 flex-wrap">
        <h1 class="text-2xl font-bold text-slate-800">📊 অ্যাক্টিভিটি লগ</h1>
        @if ($ready)
            <button type="button" @click="toggle()" class="text-xs font-semibold px-3 py-2 rounded-xl border transition" :class="auto ? 'bg-emerald-600 text-white border-emerald-600' : 'bg-white text-slate-600 border-slate-200'">
                <span x-text="auto ? '🔄 প্রতি ৩০ সেকেন্ডে রিফ্রেশ চালু' : '🔄 অটো-রিফ্রেশ'"></span>
            </button>
        @endif
    </div>


    {{-- ডায়াগনস্টিক --}}
    @php $okc = fn ($b) => $b ? 'text-emerald-600' : 'text-rose-600'; @endphp
    <details class="mb-5 bg-white rounded-2xl border border-slate-200 p-4" {{ (! $ready || ! empty($diag['last_error']) || empty($diag['middleware'])) ? 'open' : '' }}>
        <summary class="cursor-pointer font-bold text-slate-700 text-sm">🔧 ডায়াগনস্টিক (লগ কাজ না করলে এখানে দেখুন)</summary>
        <div class="mt-3 text-sm space-y-1.5">
            <p>ডাটাবেস: <b>{{ $diag['db'] }}</b> <span class="text-xs text-slate-400">(phpMyAdmin-এ ঠিক এই ডাটাবেসেই টেবিল থাকতে হবে)</span></p>
            <p class="{{ $okc($diag['table']) }}">{{ $diag['table'] ? '✅ activity_logs টেবিল আছে' : '❌ activity_logs টেবিল নেই — SQL চালিয়ে টেবিল বানান' }}@if ($diag['table']) <span class="text-slate-500">({{ $diag['rows'] }}টি লগ)</span>@endif</p>
            @if ($diag['table'])
                <p class="{{ $okc(str_starts_with((string) $diag['charset'], 'utf8')) }}">{{ str_starts_with((string) $diag['charset'], 'utf8') ? '✅' : '❌' }} টেবিলের ক্যারেক্টার সেট: <b>{{ $diag['charset'] ?? 'অজানা' }}</b>
                    @unless (str_starts_with((string) $diag['charset'], 'utf8')) — বাংলা লেখা সেভ হবে না! নিচের ALTER SQL চালান। @endunless</p>
            @endif
            <p class="{{ $okc($diag['middleware']) }}">{{ $diag['middleware'] ? '✅ পেজ-ভিজিট মিডলওয়্যার চালু আছে' : '❌ পেজ-ভিজিট মিডলওয়্যার নেই — bootstrap/app.php, app/Http/Middleware/LogPageViews.php আপলোড করুন (ও bootstrap/cache ফাঁকা করুন)' }}</p>
            <p>Cache ড্রাইভার: <b>{{ $diag['cache'] }}</b></p>
            @if (! empty($diag['last_error']))<p class="text-rose-600 break-words">⚠️ শেষ লগ-এরর: {{ $diag['last_error'] }}</p>@endif
            @if (! empty($diag['error']))<p class="text-rose-600 break-words">⚠️ {{ $diag['error'] }}</p>@endif
            <div class="pt-2 flex flex-wrap items-center gap-3">
                <form method="POST" action="{{ route('admin.activity.test') }}">@csrf<button class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-semibold">🧪 একটা পরীক্ষামূলক লগ লিখে দেখুন</button></form>
                <span class="text-xs text-slate-400">ফেল করলে ঠিক কী এরর, সেটা উপরে লাল বার্তায় দেখাবে।</span>
            </div>
            <div class="text-xs bg-slate-50 rounded-xl p-3 mt-2 text-slate-600">ক্যারেক্টার সেট ঠিক করার SQL (phpMyAdmin → SQL):<br><code class="select-all">ALTER TABLE activity_logs CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;</code></div>
        </div>
    </details>


    @unless ($ready)
        <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 text-sm text-amber-900 leading-relaxed">
            <b>⚠️ লগ এখনো চালু হয়নি।</b> উপরের ডায়াগনস্টিকে ❌ চিহ্নিত লাইনটা দেখুন, ওটাই কারণ। ঠিক করে এই পেজ রিফ্রেশ করলেই আবার যাচাই হবে।
        </div>
    @else

    {{-- সারাংশ --}}
    <div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-5">
        @foreach ([
            ['🟢 এখন অনলাইন', $online['visitors'], 'গত ৫ মিনিটে', 'border-emerald-200 bg-emerald-50 text-emerald-700'],
            ['👥 আজ ভিজিটর', $today['visitors'], 'গতকাল '.$yesterday['visitors'], 'border-indigo-200 bg-indigo-50 text-indigo-700'],
            ['🔐 লগইন করা ইউজার', $today['users'], 'অতিথি '.$today['guests'], 'border-sky-200 bg-sky-50 text-sky-700'],
            ['📄 পেজ ভিউ', $today['views'], 'গতকাল '.$yesterday['views'], 'border-slate-200 bg-slate-50 text-slate-700'],
            ['⌨️ টেস্ট দিয়েছে', $today['tests'], 'গতকাল '.$yesterday['tests'], 'border-blue-200 bg-blue-50 text-blue-700'],
            ['🆕 নতুন রেজিস্ট্রেশন', $today['registers'], 'গতকাল '.$yesterday['registers'], 'border-violet-200 bg-violet-50 text-violet-700'],
        ] as [$label, $val, $sub, $cls])
            <div class="rounded-2xl border p-3 {{ $cls }}">
                <div class="text-[11px] font-semibold leading-tight">{{ $label }}</div>
                <div class="text-2xl font-extrabold mt-1">{{ $val }}</div>
                <div class="text-[10px] opacity-70 mt-0.5">{{ $sub }}</div>
            </div>
        @endforeach
    </div>

    <div class="grid lg:grid-cols-3 gap-4 mb-5">
        {{-- ১৪ দিনের চার্ট --}}
        <div class="lg:col-span-2 bg-white rounded-2xl border border-slate-200 p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="font-bold text-slate-700 text-sm">গত ১৪ দিনের ভিজিটর</h2>
                <span class="text-[11px] text-slate-400">স্তম্ভ = ইউনিক ভিজিটর</span>
            </div>
            <div class="flex items-end gap-1.5 h-40">
                @foreach ($days as $d)
                    <div class="flex-1 flex flex-col items-center justify-end h-full group" title="{{ $d['label'] }}: {{ $d['visitors'] }} ভিজিটর, {{ $d['views'] }} পেজ ভিউ">
                        <span class="text-[10px] font-bold text-indigo-700 mb-0.5">{{ $d['visitors'] ?: '' }}</span>
                        <div class="w-full rounded-t-md bg-gradient-to-t from-indigo-500 to-sky-400 group-hover:from-indigo-600 transition-all" style="height: {{ max(2, round($d['visitors'] / $maxVisitors * 100)) }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="flex gap-1.5 mt-1.5">
                @foreach ($days as $d)<span class="flex-1 text-center text-[9px] text-slate-400">{{ \Illuminate\Support\Str::before($d['label'], ' ') }}</span>@endforeach
            </div>
        </div>

        {{-- জনপ্রিয় পেজ --}}
        <div class="bg-white rounded-2xl border border-slate-200 p-4">
            <h2 class="font-bold text-slate-700 text-sm mb-3">আজকের জনপ্রিয় পেজ</h2>
            @forelse ($topPages as $p)
                <div class="flex items-center justify-between gap-2 py-1.5 border-b border-slate-100 last:border-0 text-sm">
                    <span class="truncate text-slate-700">{{ $p->label }}</span>
                    <span class="text-xs font-bold bg-slate-100 rounded-full px-2 py-0.5">{{ $p->c }}</span>
                </div>
            @empty
                <p class="text-xs text-slate-400">আজ এখনো কোনো ডেটা নেই।</p>
            @endforelse
        </div>
    </div>

    {{-- এখন সক্রিয় ইউজার --}}
    @if ($recentUsers->isNotEmpty())
        <div class="bg-white rounded-2xl border border-slate-200 p-4 mb-5">
            <h2 class="font-bold text-slate-700 text-sm mb-3">🟢 গত ৩০ মিনিটে সক্রিয় ইউজার</h2>
            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-2">
                @foreach ($recentUsers as $r)
                    <a href="{{ route('admin.activity.index', ['user' => $r->user_id]) }}" class="rounded-xl border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50/40 p-2.5 transition">
                        <div class="text-sm font-semibold text-slate-800 truncate">{{ $r->user?->name ?? 'ইউজার #'.$r->user_id }}</div>
                        <div class="text-[11px] text-slate-500 truncate">{{ $r->label }}</div>
                        <div class="text-[10px] text-slate-400">{{ $r->created_at->diffForHumans() }}</div>
                    </a>
                @endforeach
            </div>
        </div>
    @endif

    {{-- ফিল্টার --}}
    <form method="GET" class="bg-white rounded-2xl border border-slate-200 p-3 mb-3 flex flex-wrap gap-2 items-center">
        <input type="text" name="q" value="{{ $filters['q'] }}" placeholder="নাম / ইমেইল / IP / কাজ খুঁজুন" class="rounded-xl border-slate-200 text-sm flex-1 min-w-[12rem]">
        <select name="type" class="rounded-xl border-slate-200 text-sm">
            <option value="">সব ধরনের কাজ</option>
            @foreach (\App\Models\ActivityLog::TYPES as $k => [$name])
                <option value="{{ $k }}" @selected($filters['type'] === $k)>{{ $name }}</option>
            @endforeach
        </select>
        <input type="date" name="date" value="{{ $filters['date'] }}" class="rounded-xl border-slate-200 text-sm">
        @if ($filterUser)<input type="hidden" name="user" value="{{ $filterUser->id }}">@endif
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2 text-sm font-semibold">খুঁজুন</button>
        @if ($filters['q'] || $filters['type'] || $filters['date'] || $filterUser)
            <a href="{{ route('admin.activity.index') }}" class="text-xs text-slate-500 underline">ফিল্টার মুছুন</a>
        @endif
    </form>
    @if ($filterUser)
        <div class="mb-3 text-sm rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-800 px-4 py-2">
            শুধু <b>{{ $filterUser->name }}</b> ({{ $filterUser->email }}) এর কার্যকলাপ দেখাচ্ছে।
            <a href="{{ route('admin.users.show', $filterUser) }}" class="underline font-semibold ml-2">ইউজারের প্রোফাইল →</a>
        </div>
    @endif

    {{-- লগ টেবিল --}}
    <div class="bg-white rounded-2xl border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-xs text-slate-500">
                    <tr><th class="px-3 py-2.5 text-left">সময়</th><th class="px-3 py-2.5 text-left">ইউজার</th><th class="px-3 py-2.5 text-left">কাজ</th><th class="px-3 py-2.5 text-left">বিস্তারিত</th><th class="px-3 py-2.5 text-left">ডিভাইস / IP</th></tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($logs as $l)
                        @php [$tname, $tcls] = \App\Models\ActivityLog::TYPES[$l->type] ?? [$l->type, 'bg-slate-100 text-slate-600']; @endphp
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-3 py-2 whitespace-nowrap text-xs text-slate-500">{{ $l->created_at?->format('d M, h:i:s A') }}</td>
                            <td class="px-3 py-2 whitespace-nowrap">
                                @if ($l->user)
                                    <a href="{{ route('admin.activity.index', ['user' => $l->user_id]) }}" class="font-semibold text-slate-800 hover:text-indigo-600">{{ $l->user->name }}</a>
                                @elseif ($l->user_id)
                                    <span class="text-slate-400">ইউজার #{{ $l->user_id }}</span>
                                @else
                                    <span class="text-slate-400">অতিথি <span class="text-[10px]">({{ $l->visitor }})</span></span>
                                @endif
                            </td>
                            <td class="px-3 py-2 whitespace-nowrap"><span class="text-[11px] font-bold rounded-full px-2.5 py-1 {{ $tcls }}">{{ $tname }}</span></td>
                            <td class="px-3 py-2 text-slate-700 min-w-[14rem]">{{ $l->label }}</td>
                            <td class="px-3 py-2 whitespace-nowrap text-[11px] text-slate-500">{{ $l->device === 'mobile' ? '📱' : ($l->device === 'tablet' ? '📟' : '💻') }} {{ $l->ip }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-3 py-10 text-center text-slate-400">এখনো কোনো কার্যকলাপ নেই।</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">{{ $logs->links() }}</div>

    <form method="POST" action="{{ route('admin.activity.prune') }}" class="mt-6 flex flex-wrap items-center gap-2 text-xs text-slate-500" onsubmit="return confirm('পুরনো লগ মুছে ফেলবেন?')">
        @csrf
        লগ ৯০ দিন পর নিজে থেকে মুছে যায়। এখনই মুছতে চান?
        <select name="days" class="rounded-lg border-slate-200 text-xs"><option value="7">৭ দিনের পুরনো</option><option value="30" selected>৩০ দিনের পুরনো</option><option value="60">৬০ দিনের পুরনো</option></select>
        <button class="px-3 py-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 font-semibold">মুছুন</button>
    </form>
    <p class="mt-3 text-[11px] text-slate-400 leading-relaxed">গোপনীয়তা: লগে ইউজারের IP ও ডিভাইসের ধরন রাখা হয়। পাসওয়ার্ড, টাইপ করা লেখা বা পেমেন্ট নম্বর কখনো লগে রাখা হয় না।</p>
    @endunless
</div>
@endsection
