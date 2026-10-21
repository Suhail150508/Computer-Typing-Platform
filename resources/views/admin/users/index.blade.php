@extends('admin.layouts.app')
@section('title', 'ইউজার ম্যানেজমেন্ট')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <h1 class="text-2xl font-bold text-slate-800">ইউজার ({{ number_format($users->total()) }})</h1>
        <div class="flex flex-wrap gap-2">
            <form method="GET" class="flex gap-2">
                @if ($filter)<input type="hidden" name="filter" value="{{ $filter }}">@endif
                <input type="text" name="q" value="{{ $search }}" placeholder="নাম/ইমেইল/ফোন/টাইপ কোড" class="rounded-xl border border-slate-200 px-3 py-2 text-sm w-52 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                <button class="bg-indigo-600 text-white rounded-xl px-4 text-sm font-semibold">খুঁজুন</button>
            </form>
            <a href="{{ route('admin.users.export', array_filter(['filter' => $filter, 'q' => $search])) }}" class="inline-flex items-center gap-1.5 bg-white border border-slate-200 hover:bg-slate-50 rounded-xl px-4 text-sm font-semibold text-slate-600 transition" title="বর্তমান তালিকা CSV করে ডাউনলোড">⬇ CSV</a>
        </div>
    </div>

    {{-- ফিল্টার চিপ --}}
    <div class="flex gap-2 mb-4 overflow-x-auto pb-1">
        @foreach ([
            [null, 'সব', 'all', 'slate'],
            ['trial', '🆓 ফ্রি/ট্রায়াল', 'trial', 'sky'],
            ['active', '✅ পেইড সক্রিয়', 'active', 'emerald'],
            ['expiring', '🔔 শীঘ্রই শেষ', 'expiring', 'amber'],
            ['expired', '⏰ মেয়াদ শেষ', 'expired', 'rose'],
            ['inactive', '🚫 বন্ধ', 'inactive', 'slate'],
            ['admin', '🛡 অ্যাডমিন', 'admin', 'indigo'],
        ] as [$key, $label, $ck, $color])
            <a href="{{ route('admin.users.index', array_filter(['filter' => $key, 'q' => $search])) }}"
               class="whitespace-nowrap rounded-full px-4 py-1.5 text-sm font-semibold border transition {{ ($filter ?: null) === $key ? 'bg-indigo-600 border-indigo-600 text-white shadow' : 'bg-white border-slate-200 text-slate-600 hover:border-indigo-300' }}">
                {{ $label }} <span class="opacity-70">{{ $counts[$ck] }}</span>
            </a>
        @endforeach
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">ইউজার</th>
                    <th class="text-left px-4 py-3">যোগাযোগ</th>
                    <th class="px-4 py-3">রোল</th>
                    <th class="px-4 py-3">সাবস্ক্রিপশন</th>
                    <th class="px-4 py-3">ওয়ালেট</th>
                    <th class="px-4 py-3">টেস্ট</th>
                    <th class="px-4 py-3">স্ট্যাটাস</th>
                    <th class="text-right px-4 py-3">শেষ লগইন</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($users as $u)
                    <tr class="hover:bg-slate-50 cursor-pointer" onclick="window.location='{{ route('admin.users.show', $u) }}'">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @include('partials.avatar', ['user' => $u, 'size' => 'w-9 h-9', 'text' => 'text-sm'])
                                <div>
                                    <p class="font-semibold text-slate-700">{{ $u->name }}</p>
                                    <p class="text-[11px] text-slate-400">#{{ $u->type_code }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-slate-500">{{ $u->email }}<br><span class="text-xs">{{ $u->phone ?? '—' }}</span></td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $u->role === 'admin' ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-500' }}">{{ strtoupper($u->role) }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($u->hasActiveSubscription())
                                <span class="text-xs text-slate-500">{{ $u->subscription_expires_at->format('d M Y') }}<br><b class="text-slate-700">{{ $u->daysLeft() }} দিন বাকি</b></span>
                            @else
                                <span class="text-xs text-slate-400">{{ $u->subscription_expires_at ? $u->subscription_expires_at->format('d M Y').' এ শেষ' : 'নেই' }}</span>
                            @endif
                            <form method="POST" action="{{ route('admin.users.subscription', $u) }}" class="mt-1" onsubmit="return confirm('{{ $u->name }} কে ১৫ দিনের ফ্রি মেয়াদ দিবেন?')">
                                @csrf
                                <input type="hidden" name="days" value="15">
                                <input type="hidden" name="amount_paid" value="0">
                                <input type="hidden" name="note" value="অ্যাডমিন গিফট (১৫ দিন)">
                                <button class="text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition">🎁 +১৫ দিন</button>
                            </form>
                        </td>
                        <td class="px-4 py-3 text-center">{{ \App\Support\Bn::num($u->wallet_balance) }} ৳</td>
                        <td class="px-4 py-3 text-center">{{ $u->typing_attempts_count }}</td>
                        <td class="px-4 py-3 text-center"><span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $u->statusClasses() }}">{{ $u->statusLabel() }}</span></td>
                        <td class="px-4 py-3 text-right text-xs text-slate-400">{{ $u->last_login_at?->diffForHumans() ?? '—' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">কোনো ইউজার পাওয়া যায়নি।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $users->links() }}</div>
</div>
@endsection
