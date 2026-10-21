@extends('admin.layouts.app')
@section('title', $exercise->exists ? 'শীট এডিট' : 'নতুন শীট')

@section('content')
@php $tsv = old('table_data', $exercise->exists ? $exercise->toTsv() : ''); @endphp
<div class="max-w-4xl mx-auto">
    <a href="{{ route('admin.sheet-exercises.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium">← সব শীট</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 mt-3">
        <h1 class="text-xl font-bold text-slate-800 mb-5">{{ $exercise->exists ? 'শীট এডিট করুন' : 'নতুন শীট যোগ করুন' }}</h1>

        <form method="POST" action="{{ $exercise->exists ? route('admin.sheet-exercises.update', $exercise) : route('admin.sheet-exercises.store') }}" class="space-y-4">
            @csrf
            @if ($exercise->exists) @method('PUT') @endif

            <div class="grid sm:grid-cols-4 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">শিরোনাম *</label>
                    <input type="text" name="title" value="{{ old('title', $exercise->title) }}" required placeholder="যেমন: শিক্ষার্থীর নম্বরপত্র" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ভাষা *</label>
                    <select name="language" required class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="bangla" @selected(old('language', $exercise->language ?? 'bangla') === 'bangla')>বাংলা</option>
                        <option value="english" @selected(old('language', $exercise->language ?? '') === 'english')>English</option>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">ক্রম</label>
                        <input type="number" name="order" value="{{ old('order', $exercise->order ?? 1) }}" min="0" max="999" required class="w-full rounded-xl border-slate-200 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">আইকন</label>
                        <input type="text" name="icon" value="{{ old('icon', $exercise->icon ?? '📊') }}" maxlength="10" class="w-full rounded-xl border-slate-200 text-sm">
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">বিবরণ (ঐচ্ছিক)</label>
                <input type="text" name="description" value="{{ old('description', $exercise->description) }}" maxlength="200" class="w-full rounded-xl border-slate-200 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">⏱ টাইপিংয়ের সময় *</label>
                @include('admin.partials.duration-picker', ['mode' => 'single', 'name' => 'duration_seconds', 'value' => old('duration_seconds', $exercise->duration_seconds ?? 180)])
                @error('duration_seconds') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম WPM *</label>
                    <input type="number" name="min_wpm" value="{{ old('min_wpm', $exercise->min_wpm ?? 10) }}" min="1" max="300" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম Accuracy % *</label>
                    <input type="number" name="min_accuracy" value="{{ old('min_accuracy', $exercise->min_accuracy ?? 85) }}" min="1" max="100" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম সময়% *</label>
                    <input type="number" name="min_duration_percent" value="{{ old('min_duration_percent', $exercise->min_duration_percent ?? 60) }}" min="1" max="100" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
            </div>

            {{-- শীটের ডাটা --}}
            <div x-data="sheetPreview()" x-init="parse(); $watch('raw', () => parse()); $watch('header', () => parse())">
                <div class="flex flex-wrap items-center justify-between gap-2 mb-1.5">
                    <label class="text-xs font-semibold text-slate-500">📋 শীটের ডাটা * <span class="font-normal text-slate-400">— Excel/Google Sheets থেকে কপি করে এখানে পেস্ট করুন (বা কমা দিয়ে CSV লিখুন)</span></label>
                    <label class="flex items-center gap-2 text-xs font-semibold text-slate-600">
                        <input type="checkbox" name="has_header" value="1" x-model="header" class="rounded border-slate-300 text-indigo-600">
                        প্রথম সারি = কলামের নাম (হেডার)
                    </label>
                </div>
                <textarea name="table_data" x-model="raw" rows="9" required class="w-full rounded-xl border-slate-200 text-sm font-mono leading-relaxed whitespace-pre"
                          placeholder="নাম&#9;রোল&#9;বাংলা&#9;ইংরেজি&#10;রহিম উদ্দিন&#9;১০১&#9;৭৮&#9;৮২">{{ $tsv }}</textarea>
                @error('table_data') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-[11px] mt-1" :class="info.ok ? 'text-slate-400' : 'text-amber-600'" x-text="info.text"></p>

                <div class="mt-3" x-show="rows.length" x-cloak>
                    <p class="text-xs font-semibold text-slate-500 mb-1">👁 প্রিভিউ — ইউজার এভাবেই দেখবে</p>
                    <div class="overflow-auto max-h-64 rounded-xl border border-slate-200">
                        <table class="w-full text-xs">
                            <thead x-show="header && head.length" class="bg-sky-50 text-sky-800 sticky top-0">
                                <tr><template x-for="(h, i) in head" :key="i"><th class="px-3 py-1.5 text-left font-semibold" x-text="h"></th></template></tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100">
                                <template x-for="(row, ri) in rows" :key="ri">
                                    <tr><template x-for="(cell, ci) in row" :key="ci"><td class="px-3 py-1.5 whitespace-nowrap" x-text="cell"></td></template></tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $exercise->exists ? $exercise->is_active : true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                সক্রিয় (ইউজাররা শীট টাইপিংয়ে পাবে)
            </label>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold transition">
                {{ $exercise->exists ? 'আপডেট করুন' : 'তৈরি করুন' }}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // লাইভ প্রিভিউ — সার্ভারের parseTable() এর মতোই: ট্যাব থাকলে ট্যাব, নইলে কমা দিয়ে ভাগ
    function sheetPreview() {
        return {
            raw: @json($tsv),
            header: {{ (session()->hasOldInput() ? (bool) old('has_header') : ($exercise->exists ? ! empty($exercise->headers) : true)) ? 'true' : 'false' }},
            head: [], rows: [], info: { ok: true, text: '' },
            parse: function () {
                var text = (this.raw || '').replace(/\r\n?/g, '\n').trim();
                var lines = text === '' ? [] : text.split('\n').filter(function (l) { return l.trim() !== ''; });
                var delim = text.indexOf('\t') !== -1 ? '\t' : ',';
                var table = lines.map(function (l) { return l.split(delim).map(function (c) { return c.trim(); }); });
                var width = table.reduce(function (m, r) { return Math.max(m, r.length); }, 0);
                table = table.map(function (r) { while (r.length < width) { r.push(''); } return r; });
                this.head = this.header && table.length ? table.shift() : [];
                this.rows = table;
                var cells = this.rows.length * width;
                var problems = [];
                if (this.rows.length > {{ \App\Models\SheetExercise::MAX_ROWS }}) { problems.push('সারি সর্বোচ্চ {{ \App\Models\SheetExercise::MAX_ROWS }}টি'); }
                if (width > {{ \App\Models\SheetExercise::MAX_COLS }}) { problems.push('কলাম সর্বোচ্চ {{ \App\Models\SheetExercise::MAX_COLS }}টি'); }
                this.info = problems.length
                    ? { ok: false, text: '⚠️ ' + problems.join(', ') + ' দেওয়া যাবে।' }
                    : { ok: true, text: this.rows.length ? ('✅ ' + this.rows.length + ' সারি × ' + width + ' কলাম = ' + cells + 'টি ঘর টাইপ করতে হবে।') : 'ডাটা দিলে প্রিভিউ দেখাবে।' };
            }
        };
    }
</script>
@endpush
