@extends('admin.layouts.app')
@section('title', 'ওয়ালেট')

@section('content')
<div class="max-w-6xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">👛 ওয়ালেট</h1>
    <p class="text-sm text-slate-500 mb-5">সব ইউজারের ওয়ালেট লেনদেন। কারো ব্যালেন্স বদলাতে চাইলে ইউজারের প্রোফাইল পেজ থেকে জমা/কর্তন করুন।</p>

    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-5">
        @foreach ([
            ['মোট ব্যালেন্স (সব ইউজার)', \App\Support\Bn::num($totals['balance']).' ৳', 'from-emerald-500 to-teal-500'],
            ['মোট জমা', \App\Support\Bn::num($totals['credit']).' ৳', 'from-sky-500 to-indigo-500'],
            ['মোট কর্তন', \App\Support\Bn::num($totals['debit']).' ৳', 'from-rose-500 to-orange-500'],
            ['ব্যালেন্স আছে এমন ইউজার', $totals['holders'], 'from-violet-500 to-fuchsia-500'],
        ] as [$label, $value, $grad])
            <div class="rounded-2xl bg-gradient-to-br {{ $grad }} text-white p-4 shadow-lg anim-fade-up">
                <p class="text-xs opacity-80">{{ $label }}</p>
                <p class="text-2xl font-bold mt-1">{{ $value }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="flex flex-wrap gap-2 mb-4">
        <input type="text" name="q" value="{{ $search }}" placeholder="নাম / ইমেইল / ফোন / টাইপ কোড" class="flex-1 min-w-[200px] rounded-xl border-slate-200 text-sm">
        <select name="type" class="rounded-xl border-slate-200 text-sm">
            <option value="">সব ধরন</option>
            <option value="credit" @selected($type === 'credit')>জমা</option>
            <option value="debit" @selected($type === 'debit')>কর্তন</option>
        </select>
        <button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 text-sm font-semibold transition">খুঁজুন</button>
    </form>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">ইউজার</th>
                    <th class="px-4 py-3">ধরন</th>
                    <th class="px-4 py-3">পরিমাণ</th>
                    <th class="px-4 py-3">ব্যালেন্স (পরে)</th>
                    <th class="text-left px-4 py-3">কারণ</th>
                    <th class="px-4 py-3">কে করেছে</th>
                    <th class="text-right px-4 py-3">সময়</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($transactions as $t)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            @if ($t->user)
                                <a href="{{ route('admin.users.show', $t->user) }}" class="font-semibold text-indigo-600 hover:underline">{{ $t->user->name }}</a>
                                <p class="text-[11px] text-slate-400">#{{ $t->user->type_code }}</p>
                            @else
                                <span class="text-slate-400">মুছে ফেলা ইউজার</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold px-2.5 py-1 rounded-full {{ $t->isCredit() ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ $t->isCredit() ? 'জমা' : 'কর্তন' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center font-bold {{ $t->isCredit() ? 'text-emerald-600' : 'text-rose-600' }}">{{ $t->isCredit() ? '+' : '-' }}{{ \App\Support\Bn::num($t->amount) }} ৳</td>
                        <td class="px-4 py-3 text-center">{{ \App\Support\Bn::num($t->balance_after) }} ৳</td>
                        <td class="px-4 py-3 text-slate-600 max-w-[16rem] truncate" title="{{ $t->reason }}">{{ $t->reason ?? '—' }}</td>
                        <td class="px-4 py-3 text-center text-xs text-slate-500">{{ $t->admin?->name ?? 'সিস্টেম' }}</td>
                        <td class="px-4 py-3 text-right text-xs text-slate-400">{{ $t->created_at->format('d M Y, h:i A') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-slate-400 py-8">কোনো ওয়ালেট লেনদেন নেই।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $transactions->links() }}</div>
</div>
@endsection
