@extends('admin.layouts.app')
@section('title', 'টাইপিং শুরু কার্ড')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-bold text-slate-800">টাইপিং শুরু কার্ড ({{ number_format($cards->total()) }})</h1>
        <div class="flex gap-2">
            <form method="GET" class="flex gap-2">
                <select name="tier" class="rounded-xl border-slate-200 text-sm" onchange="this.form.submit()">
                    <option value="">সব টায়ার</option>
                    <option value="basic" @selected($tier === 'basic')>বেসিক</option>
                    <option value="advance" @selected($tier === 'advance')>অ্যাডভান্স</option>
                    <option value="hard" @selected($tier === 'hard')>হার্ড</option>
                </select>
                <select name="language" class="rounded-xl border-slate-200 text-sm" onchange="this.form.submit()">
                    <option value="">সব ভাষা</option>
                    <option value="bangla" @selected($language === 'bangla')>বাংলা</option>
                    <option value="english" @selected($language === 'english')>English</option>
                </select>
            </form>
            <a href="{{ route('admin.practice-cards.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2.5 text-sm font-semibold transition whitespace-nowrap">+ নতুন কার্ড</a>
        </div>
    </div>

    {{-- একসাথে সময় বদলান --}}
    <div x-data="{ open: false }" class="mb-4 rounded-2xl border border-indigo-100 bg-indigo-50/60">
        <button type="button" @click="open = !open" class="w-full flex items-center justify-between px-4 py-3 text-sm font-bold text-indigo-700">
            <span>⏱ একসাথে অনেক কার্ডের সময় বদলান</span>
            <svg class="w-4 h-4 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m6 9 6 6 6-6"/></svg>
        </button>
        <form x-show="open" x-cloak x-transition method="POST" action="{{ route('admin.practice-cards.bulkDuration') }}" class="px-4 pb-4 space-y-3"
              onsubmit="return confirm('নির্বাচিত সব কার্ডের সময় বদলে যাবে। নিশ্চিত?')">
            @csrf
            <div class="grid sm:grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">কোন কার্ডগুলোর?</label>
                    <select name="tier" class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">সব টায়ার</option>
                        <option value="basic" @selected($tier === 'basic')>বেসিক</option>
                        <option value="advance" @selected($tier === 'advance')>অ্যাডভান্স</option>
                        <option value="hard" @selected($tier === 'hard')>হার্ড</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ভাষা</label>
                    <select name="language" class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">সব ভাষা</option>
                        <option value="bangla" @selected($language === 'bangla')>বাংলা</option>
                        <option value="english" @selected($language === 'english')>English</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">নতুন সময়</label>
                @include('admin.partials.duration-picker', ['mode' => 'single', 'name' => 'duration_seconds', 'value' => 600])
            </div>
            <p class="text-[11px] text-amber-700">⚠️ সময় বাড়ালে কার্ডের কনটেন্টও যথেষ্ট লম্বা আছে কি না দেখে নিন (প্রতিটি কার্ড এডিট করলে ইঙ্গিত দেখাবে)।</p>
            <button class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-5 py-2.5 text-sm font-semibold transition">সময় প্রয়োগ করুন</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">টায়ার / #</th>
                    <th class="text-left px-4 py-3">শিরোনাম</th>
                    <th class="px-4 py-3">ভাষা</th>
                    <th class="px-4 py-3">সময়</th>
                    <th class="px-4 py-3">WPM</th>
                    <th class="px-4 py-3">Accuracy</th>
                    <th class="px-4 py-3">স্ট্যাটাস</th>
                    <th class="text-right px-4 py-3">কাজ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($cards as $c)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $c->tierLabel() }} #{{ $c->order }}</span>
                        </td>
                        <td class="px-4 py-3 font-semibold text-slate-700 max-w-xs truncate">{{ $c->icon }} {{ $c->title }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $c->language === 'bangla' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }}">{{ $c->language === 'bangla' ? 'Bangla' : 'English' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ \App\Support\Bn::minutes($c->duration_seconds) }} মিনিট</td>
                        <td class="px-4 py-3 text-center">{{ $c->min_wpm }}</td>
                        <td class="px-4 py-3 text-center">{{ $c->min_accuracy }}%</td>
                        <td class="px-4 py-3 text-center">
                            @if ($c->is_active)
                                <span class="text-xs font-bold text-emerald-600">সক্রিয়</span>
                            @else
                                <span class="text-xs font-bold text-slate-400">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.practice-cards.edit', $c) }}" class="text-xs font-semibold text-indigo-600 hover:underline">এডিট</a>
                                <form method="POST" action="{{ route('admin.practice-cards.destroy', $c) }}" onsubmit="return confirm('এই কার্ডটি মুছে ফেলতে চান?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">মুছুন</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">কোনো কার্ড পাওয়া যায়নি।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $cards->links() }}</div>
</div>
@endsection
