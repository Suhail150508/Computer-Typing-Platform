@extends('admin.layouts.app')
@section('title', 'ল্যাব ম্যানেজমেন্ট')

@section('content')
<div class="max-w-5xl mx-auto">
    <div class="flex items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-bold text-slate-800">ল্যাব ({{ $labs->count() }})</h1>
        <a href="{{ route('admin.labs.create') }}" class="bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl px-4 py-2.5 text-sm font-semibold transition">+ নতুন ল্যাব</a>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">ক্রম</th>
                    <th class="text-left px-4 py-3">নাম</th>
                    <th class="text-left px-4 py-3">ব্যাজ</th>
                    <th class="px-4 py-3">ন্যূনতম WPM</th>
                    <th class="px-4 py-3">Accuracy</th>
                    <th class="px-4 py-3">প্যাসেজ</th>
                    <th class="px-4 py-3">স্ট্যাটাস</th>
                    <th class="text-right px-4 py-3">কাজ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($labs as $lab)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-slate-400">{{ $lab->sort_order }}</td>
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $lab->name_bn }}<br><span class="text-xs text-slate-400 font-normal">{{ $lab->name_en }}</span></td>
                        <td class="px-4 py-3 text-slate-500">{{ $lab->badge_text }}</td>
                        <td class="px-4 py-3 text-center">{{ $lab->min_wpm }}</td>
                        <td class="px-4 py-3 text-center">{{ $lab->min_accuracy }}%</td>
                        <td class="px-4 py-3 text-center">
                            <a href="{{ route('admin.passages.index', ['lab' => $lab->id]) }}" class="text-indigo-600 hover:underline">{{ $lab->passages_count }}টি</a>
                        </td>
                        <td class="px-4 py-3 text-center">
                            @if ($lab->is_active)
                                <span class="text-xs font-bold text-emerald-600">সক্রিয়</span>
                            @else
                                <span class="text-xs font-bold text-slate-400">নিষ্ক্রিয়</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.labs.edit', $lab) }}" class="text-xs font-semibold text-indigo-600 hover:underline">এডিট</a>
                                <form method="POST" action="{{ route('admin.labs.destroy', $lab) }}" onsubmit="return confirm('এই ল্যাবটি মুছে ফেলতে চান?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">মুছুন</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">কোনো ল্যাব নেই। "নতুন ল্যাব" চেপে যোগ করুন।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
