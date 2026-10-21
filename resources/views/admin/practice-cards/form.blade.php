@extends('admin.layouts.app')
@section('title', $card->exists ? 'কার্ড এডিট' : 'নতুন কার্ড')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.practice-cards.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium">← সব কার্ড</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 mt-3">
        <h1 class="text-xl font-bold text-slate-800 mb-5">{{ $card->exists ? 'কার্ড এডিট করুন' : 'নতুন কার্ড যোগ করুন' }}</h1>

        <form method="POST" action="{{ $card->exists ? route('admin.practice-cards.update', $card) : route('admin.practice-cards.store') }}" class="space-y-4">
            @csrf
            @if ($card->exists) @method('PUT') @endif

            <div class="grid sm:grid-cols-4 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">টায়ার *</label>
                    <select name="tier" required class="w-full rounded-xl border-slate-200 text-sm">
                        @foreach (\App\Models\PracticeCard::TIERS as $t)
                            <option value="{{ $t }}" @selected(old('tier', $card->tier ?? 'basic') === $t)>{{ ucfirst($t) }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ভাষা *</label>
                    <select name="language" required class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="bangla" @selected(old('language', $card->language ?? 'bangla') === 'bangla')>বাংলা</option>
                        <option value="english" @selected(old('language', $card->language ?? '') === 'english')>English</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ক্রম নং *</label>
                    <input type="number" name="order" value="{{ old('order', $card->order ?? 1) }}" min="1" max="999" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">আইকন (ইমোজি)</label>
                    <input type="text" name="icon" value="{{ old('icon', $card->icon ?? '⌨️') }}" maxlength="10" class="w-full rounded-xl border-slate-200 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">শিরোনাম *</label>
                <input type="text" name="title" value="{{ old('title', $card->title) }}" required placeholder="যেমন: বেসিক #১ — ঘুম থেকে ওঠা" class="w-full rounded-xl border-slate-200 text-sm">
                @error('title') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">বিবরণ (ঐচ্ছিক)</label>
                <input type="text" name="description" value="{{ old('description', $card->description) }}" maxlength="200" class="w-full rounded-xl border-slate-200 text-sm">
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">⏱ টাইপিংয়ের সময় *</label>
                @include('admin.partials.duration-picker', ['mode' => 'single', 'name' => 'duration_seconds', 'value' => old('duration_seconds', $card->duration_seconds ?? 60)])
                @error('duration_seconds') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম WPM *</label>
                    <input type="number" name="min_wpm" value="{{ old('min_wpm', $card->min_wpm ?? 10) }}" min="1" max="300" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম Accuracy %*</label>
                    <input type="number" name="min_accuracy" value="{{ old('min_accuracy', $card->min_accuracy ?? 85) }}" min="1" max="100" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম সময়% *</label>
                    <input type="number" name="min_duration_percent" value="{{ old('min_duration_percent', $card->min_duration_percent ?? 80) }}" min="1" max="100" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">কনটেন্ট * <span class="font-normal text-slate-400">(একাধিক অনুচ্ছেদ আলাদা করতে ফাঁকা লাইন দিন — সময়ের তুলনায় যথেষ্ট লম্বা রাখুন)</span></label>
                <textarea name="content" rows="10" required class="w-full rounded-xl border-slate-200 text-sm font-mono leading-relaxed">{{ old('content', $card->content) }}</textarea>
                @error('content') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                <p id="length-hint" class="text-xs mt-1.5 rounded-lg px-3 py-2 bg-slate-50 text-slate-500"></p>
            </div>

            <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $card->exists ? $card->is_active : true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                সক্রিয় (ইউজাররা টাইপিং শুরুে পাবে)
            </label>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold transition">
                {{ $card->exists ? 'আপডেট করুন' : 'তৈরি করুন' }}
            </button>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // কনটেন্ট সময়ের তুলনায় যথেষ্ট লম্বা কি না — লাইভ ইঙ্গিত (ইউজার সময় শেষের আগেই প্যাসেজ শেষ করে ফেললে ফলাফল ঠিকমতো আসে না)
    (function () {
        var hint = document.getElementById('length-hint');
        var area = document.querySelector('textarea[name=content]');
        var wpm = document.querySelector('input[name=min_wpm]');
        var dur = document.getElementById('duration-seconds-input');
        function refresh() {
            var secs = parseInt(dur && dur.value, 10) || 0;
            var minWpm = parseInt(wpm.value, 10) || 0;
            var have = Array.from(area.value.replace(/\s+/g, '')).length;
            var need = Math.round(minWpm * 5 * (secs / 60) * 1.5); // ন্যূনতম গতির ১.৫ গুণ দ্রুত টাইপিস্টের জন্যও যেন যথেষ্ট থাকে
            var ok = have >= need;
            hint.className = 'text-xs mt-1.5 rounded-lg px-3 py-2 ' + (ok ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700');
            hint.textContent = (ok ? '✅ ' : '⚠️ ') + 'কনটেন্টে এখন ' + have + ' অক্ষর (স্পেস ছাড়া)। ' + (secs / 60) + ' মিনিটের জন্য কমপক্ষে প্রায় ' + need + ' অক্ষর রাখা ভালো' + (ok ? '।' : ' — আরও লেখা যোগ করুন।');
        }
        ['input', 'change', 'keyup'].forEach(function (e) { area.addEventListener(e, refresh); wpm.addEventListener(e, refresh); });
        setInterval(refresh, 500); // পিকারের বাটন (Alpine) দিয়ে সময় বদলালেও হালনাগাদ হবে
        refresh();
    })();
</script>
@endpush
