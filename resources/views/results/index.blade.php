@extends('layouts.app')
@section('title', 'টাইপিং রেজাল্ট')

@section('content')
<div class="max-w-6xl mx-auto" x-data="resultModal()" @keydown.escape.window="close()">
    <div class="bg-white rounded-3xl shadow-lg shadow-slate-200/70 border border-slate-100 p-3 sm:p-6 anim-fade-up">
        <div class="text-center mb-5">
            <h1 class="inline-block bg-white rounded-2xl shadow-md px-5 sm:px-8 py-3 text-xl sm:text-2xl font-bold text-indigo-900 border-b-4 border-rose-500">
                টাইপিং রেজাল্ট | TypingMoja
            </h1>
        </div>

        {{-- টুলবার --}}
        <div class="flex flex-wrap items-center gap-2 sm:gap-3 mb-4">
            <a href="{{ route('dashboard') }}" class="rounded-full border border-slate-200 px-4 py-1.5 text-sm font-semibold hover:bg-slate-50 transition">← Back</a>

            <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-500 text-white text-xs font-bold px-3 py-1.5">
                PASS: <span class="bg-white text-emerald-600 rounded-full px-1.5">{{ $summary['pass'] }}</span> <span class="opacity-80">({{ $summary['pass_pct'] }}%)</span>
            </span>
            <span class="inline-flex items-center gap-1.5 rounded-full bg-rose-500 text-white text-xs font-bold px-3 py-1.5">
                FAIL: <span class="bg-white text-rose-600 rounded-full px-1.5">{{ $summary['fail'] }}</span> <span class="opacity-80">({{ $summary['fail_pct'] }}%)</span>
            </span>

            @if ($summary['total'] > 0)
                <form method="POST" action="{{ route('results.destroyAll') }}" onsubmit="return confirm('সব রেজাল্ট মুছে ফেলতে চান? এটি ফেরত আনা যাবে না।')">
                    @csrf @method('DELETE')
                    <button class="inline-flex items-center gap-1.5 rounded-full bg-white border border-slate-200 shadow px-4 py-1.5 text-sm font-semibold hover:bg-rose-50 hover:text-rose-600 transition">🗑 Delete All</button>
                </form>
            @endif

            <form method="GET" action="{{ route('results.index') }}" class="ml-auto flex flex-1 sm:flex-none items-center gap-2 min-w-[240px]">
                <select name="lab" class="field !py-2 !rounded-xl text-sm max-w-[9rem]" onchange="this.form.submit()">
                    <option value="">সব ল্যাব</option>
                    @foreach ($labs as $l)
                        <option value="{{ $l->id }}" @selected($labId === $l->id)>{{ $l->name_bn }}</option>
                    @endforeach
                </select>
                <div class="flex flex-1 rounded-xl border border-slate-200 overflow-hidden bg-white">
                    <input type="text" name="q" value="{{ $search }}" placeholder="Search language..." class="flex-1 min-w-0 px-3 py-2 text-sm outline-none">
                    <button class="bg-violet-500 hover:bg-violet-600 text-white px-4 text-sm font-semibold transition">Search</button>
                </div>
            </form>
        </div>

        {{-- টেবিল --}}
        <div class="overflow-x-auto rounded-2xl border border-slate-200">
            <table class="w-full text-sm text-center whitespace-nowrap">
                <thead class="bg-slate-100 text-slate-700 text-xs uppercase tracking-wide">
                    <tr>
                        <th class="px-3 py-3.5">তারিখ ও সময়</th>
                        <th class="px-3 py-3.5">ল্যাব / কার্ড</th>
                        <th class="px-3 py-3.5">ভাষা</th>
                        <th class="px-3 py-3.5">WPM</th>
                        <th class="px-3 py-3.5">Accuracy</th>
                        <th class="px-3 py-3.5">সময়</th>
                        <th class="px-3 py-3.5">ভুল (শব্দ)</th>
                        <th class="px-3 py-3.5">ভুল (%)</th>
                        <th class="px-3 py-3.5">Results &amp; View</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse ($attempts as $a)
                        <tr class="hover:bg-blue-50/50 transition anim-fade-in">
                            <td class="px-3 py-3">{{ $a->created_at->format('d-M | h:i:s A') }}</td>
                            <td class="px-3 py-3 text-slate-600">{{ $a->sourceLabel() }}</td>
                            <td class="px-3 py-3"><span class="inline-block bg-cyan-700 text-white text-xs font-semibold rounded-full px-3 py-1">{{ $a->language === 'bangla' ? 'Bangla' : 'English' }}</span></td>
                            <td class="px-3 py-3 font-semibold">{{ \App\Support\Bn::num($a->wpm) }}</td>
                            <td class="px-3 py-3">{{ \App\Support\Bn::num($a->accuracy) }}%</td>
                            <td class="px-3 py-3">{{ $a->timeLabel() }}</td>
                            <td class="px-3 py-3">{{ $a->wrong_words }}</td>
                            <td class="px-3 py-3">{{ \App\Support\Bn::num($a->error_percent) }}%</td>
                            <td class="px-3 py-3">
                                <div class="flex items-center justify-center gap-1.5">
                                    <span class="inline-block min-w-[3.6rem] rounded-full text-white text-xs font-bold px-3 py-1.5 {{ $a->isPass() ? 'bg-green-600' : 'bg-red-600' }}">{{ $a->isPass() ? 'PASS' : 'FAIL' }}</span>
                                    <button type="button" @click="open('{{ route('results.show', $a) }}')" class="w-9 h-9 grid place-items-center rounded-xl bg-blue-500 hover:bg-blue-600 text-white transition" title="বিস্তারিত দেখুন" aria-label="বিস্তারিত দেখুন">👁</button>
                                    <form method="POST" action="{{ route('results.destroy', $a) }}" onsubmit="return confirm('এই রেজাল্টটি মুছে ফেলবেন?')">
                                        @csrf @method('DELETE')
                                        <button class="w-9 h-9 grid place-items-center rounded-xl bg-rose-50 hover:bg-rose-100 text-rose-600 border border-rose-100 transition" title="মুছুন" aria-label="মুছুন">🗑</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="px-3 py-14 text-slate-500">
                                <div class="text-4xl mb-2">⌨️</div>
                                কোনো রেজাল্ট পাওয়া যায়নি।
                                <a href="{{ route('dashboard') }}" class="text-brand-600 font-semibold hover:underline">প্রথম টেস্ট দিন →</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">{{ $attempts->links() }}</div>
    </div>

    {{-- ডিটেইল মডাল --}}
    <div x-show="visible" x-cloak class="fixed inset-0 z-[60] flex items-start sm:items-center justify-center p-2 sm:p-6" aria-modal="true" role="dialog">
        <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" x-show="visible" x-transition.opacity @click="close()"></div>
        <div x-show="visible" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 translate-y-4 scale-95" x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             class="relative bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[94vh] overflow-y-auto p-3 sm:p-6">
            <button type="button" @click="close()" class="sticky top-0 float-right z-10 w-9 h-9 grid place-items-center rounded-full bg-slate-100 hover:bg-slate-200 text-xl" aria-label="বন্ধ করুন">&times;</button>
            <div x-show="loading" class="py-24 text-center text-slate-500">
                <div class="mx-auto w-10 h-10 rounded-full border-4 border-blue-200 border-t-brand-600 animate-spin"></div>
                <p class="mt-3 text-sm">লোড হচ্ছে...</p>
            </div>
            <p x-show="error" x-cloak class="py-16 text-center text-rose-600" x-text="error"></p>
            <div x-html="html"></div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function resultModal() {
        return {
            visible: false, loading: false, html: '', error: '',
            async open(url) {
                this.visible = true; this.loading = true; this.html = ''; this.error = '';
                document.body.classList.add('overflow-hidden');
                try {
                    const res = await fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' } });
                    if (!res.ok) { throw new Error('status ' + res.status); }
                    this.html = await res.text();
                } catch (e) {
                    this.error = 'রেজাল্ট লোড করা যায়নি। আবার চেষ্টা করুন।';
                } finally {
                    this.loading = false;
                }
            },
            close() { this.visible = false; document.body.classList.remove('overflow-hidden'); },
        };
    }
</script>
@endpush
@endsection
