@extends('admin.layouts.app')
@section('title', 'প্যাসেজ ম্যানেজমেন্ট')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-bold text-slate-800">প্যাসেজ ({{ number_format($passages->total()) }})</h1>
        <div class="flex gap-2">
            <form method="GET">
                <select name="lab" class="rounded-xl border-slate-200 text-sm" onchange="this.form.submit()">
                    <option value="">সব ল্যাব</option>
                    @foreach ($labs as $l)
                        <option value="{{ $l->id }}" @selected($labId === $l->id)>{{ $l->name_bn }}</option>
                    @endforeach
                </select>
            </form>
            <a href="{{ route('admin.passages.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2.5 text-sm font-semibold transition">+ নতুন প্যাসেজ</a>
        </div>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">শিরোনাম</th>
                    <th class="text-left px-4 py-3">ল্যাব</th>
                    <th class="px-4 py-3">ভাষা</th>
                    <th class="px-4 py-3">শব্দ</th>
                    <th class="px-4 py-3">ক্যারেক্টার</th>
                    <th class="px-4 py-3">স্ট্যাটাস</th>
                    <th class="text-right px-4 py-3">কাজ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($passages as $p)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-700 max-w-xs truncate">{{ $p->title ?: '(শিরোনামহীন)' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $p->lab?->name_bn ?? '—' }}</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $p->language === 'bangla' ? 'bg-emerald-100 text-emerald-700' : 'bg-sky-100 text-sky-700' }}">{{ $p->language === 'bangla' ? 'Bangla' : 'English' }}</span>
                        </td>
                        <td class="px-4 py-3 text-center">{{ $p->word_count }}</td>
                        <td class="px-4 py-3 text-center">{{ $p->char_count }}</td>
                        <td class="px-4 py-3 text-center">
                            @if ($p->is_active)
                                <span class="text-xs font-bold text-emerald-600">সক্রিয়</span>
                            @else
                                <span class="text-xs font-bold text-slate-400">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.passages.edit', $p) }}" class="text-xs font-semibold text-indigo-600 hover:underline">এডিট</a>
                                <form method="POST" action="{{ route('admin.passages.destroy', $p) }}" onsubmit="return confirm('এই প্যাসেজটি মুছে ফেলতে চান?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">মুছুন</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-center text-slate-400 py-8">কোনো প্যাসেজ পাওয়া যায়নি।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $passages->links() }}</div>
</div>
@endsection
