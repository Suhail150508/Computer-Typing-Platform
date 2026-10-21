@extends('admin.layouts.app')
@section('title', 'টাইপিং অ্যাটেম্পট')

@section('content')
<div class="max-w-6xl mx-auto">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
        <h1 class="text-2xl font-bold text-slate-800">টাইপিং অ্যাটেম্পট ({{ number_format($attempts->total()) }})</h1>
        <form method="GET" class="flex flex-wrap gap-2">
            <select name="result" class="rounded-xl border-slate-200 text-sm" onchange="this.form.submit()">
                <option value="">সব ফলাফল</option>
                <option value="pass" @selected($result === 'pass')>PASS</option>
                <option value="fail" @selected($result === 'fail')>FAIL</option>
            </select>
            <select name="source" class="rounded-xl border-slate-200 text-sm" onchange="this.form.submit()">
                <option value="">সব উৎস</option>
                <option value="lab" @selected($source === 'lab')>টাইপিং ল্যাব</option>
                <option value="practice" @selected($source === 'practice')>টাইপিং শুরু</option>
            </select>
            <input type="text" name="q" value="{{ $search }}" placeholder="ইউজার নাম/ইমেইল" class="rounded-xl border-slate-200 text-sm w-44">
            <button class="bg-indigo-600 text-white rounded-xl px-4 text-sm font-semibold">খুঁজুন</button>
        </form>
    </div>

    <div class="bg-white rounded-2xl border border-slate-200 overflow-x-auto">
        <table class="w-full text-sm whitespace-nowrap">
            <thead class="text-xs text-slate-400 uppercase bg-slate-50">
                <tr>
                    <th class="text-left px-4 py-3">ইউজার</th>
                    <th class="text-left px-4 py-3">উৎস</th>
                    <th class="px-4 py-3">ভাষা</th>
                    <th class="px-4 py-3">WPM</th>
                    <th class="px-4 py-3">Accuracy</th>
                    <th class="px-4 py-3">ফলাফল</th>
                    <th class="px-4 py-3">সময়</th>
                    <th class="text-right px-4 py-3">কাজ</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                @forelse ($attempts as $a)
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 font-semibold text-slate-700">{{ $a->user?->name ?? '—' }}</td>
                        <td class="px-4 py-3 text-slate-500">{{ $a->sourceLabel() }}</td>
                        <td class="px-4 py-3 text-center">{{ $a->language === 'bangla' ? 'Bangla' : 'English' }}</td>
                        <td class="px-4 py-3 text-center">{{ \App\Support\Bn::num($a->wpm) }}</td>
                        <td class="px-4 py-3 text-center">{{ \App\Support\Bn::num($a->accuracy) }}%</td>
                        <td class="px-4 py-3 text-center">
                            <span class="text-xs font-bold px-2 py-0.5 rounded-full {{ $a->isPass() ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-700' }}">{{ strtoupper($a->result) }}</span>
                        </td>
                        <td class="px-4 py-3 text-slate-400 text-xs">{{ $a->created_at->format('d M, h:i A') }}</td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.attempts.show', $a) }}" class="text-xs font-semibold text-indigo-600 hover:underline">দেখুন</a>
                                <form method="POST" action="{{ route('admin.attempts.destroy', $a) }}" onsubmit="return confirm('এই রেকর্ডটি মুছে ফেলতে চান?')">
                                    @csrf @method('DELETE')
                                    <button class="text-xs font-semibold text-rose-600 hover:underline">মুছুন</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-slate-400 py-8">কোনো অ্যাটেম্পট পাওয়া যায়নি।</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $attempts->links() }}</div>
</div>
@endsection
