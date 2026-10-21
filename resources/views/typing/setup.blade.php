@extends('layouts.base')
@section('title', 'টেস্ট সেটআপ')

@section('body')
    @include('partials.flash')
    <main class="min-h-screen grid place-items-center px-4 py-10">
        <div class="w-full max-w-md anim-fade-up">
            <a href="{{ route('dashboard') }}" class="inline-flex items-center gap-1 text-sm text-slate-500 hover:text-brand-600 mb-3 transition">
                <span>←</span> ড্যাশবোর্ডে ফিরে যান
            </a>

            <div class="bg-white rounded-3xl border border-blue-300/60 shadow-xl shadow-blue-200/50 p-6 sm:p-8"
                 x-data="{ lang: '{{ $language }}', loading: false }">
                <h1 class="text-center text-xl sm:text-2xl font-bold">
                    টাইপিং টেস্ট — <span x-text="lang === 'bangla' ? 'বাংলা' : 'English'"></span>
                </h1>
                <p class="text-center text-rose-700 font-semibold mt-1">{{ $lab->name_bn }}</p>

                <form method="POST" action="{{ route('typing.start') }}" class="mt-6 space-y-5" @submit="loading = true">
                    @csrf

                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-1.5" for="lab">ল্যাব নির্বাচন করুন:</label>
                        <select id="lab" name="lab_id" class="field"
                                onchange="window.location.href = this.options[this.selectedIndex].dataset.url + '?lang=' + document.querySelector('[name=language]:checked').value">
                            @foreach ($labs as $l)
                                <option value="{{ $l->id }}" data-url="{{ route('typing.setup', $l) }}" @selected($l->id === $lab->id)>{{ $l->name_bn }}</option>
                            @endforeach
                        </select>
                        @error('lab_id') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <span class="block text-sm font-semibold text-slate-600 mb-1.5">ভাষা (Language):</span>
                        <div class="grid grid-cols-2 gap-2 bg-slate-100 rounded-2xl p-1">
                            @foreach (config('typing.languages') as $key => $label)
                                <label class="cursor-pointer">
                                    <input type="radio" name="language" value="{{ $key }}" class="sr-only" x-model="lang">
                                    <span class="block text-center rounded-xl py-2 font-semibold text-sm transition"
                                          :class="lang === '{{ $key }}' ? 'bg-white shadow text-brand-700' : 'text-slate-500'">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('language') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-slate-600 mb-1.5" for="duration">সময় (Time):</label>
                        <select id="duration" name="duration" class="field">
                            @foreach ($durations as $d)
                                <option value="{{ $d }}" @selected($d === (in_array(5, $durations, true) ? 5 : $durations[0]))>{{ \App\Support\Bn::digits($d) }} মিনিট</option>
                            @endforeach
                        </select>
                        @error('duration') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>

                    <div class="rounded-2xl bg-blue-50 border border-blue-100 px-4 py-3 text-xs text-slate-600 leading-relaxed">
                        <b>পাস করার শর্ত:</b> ন্যূনতম {{ \App\Support\Bn::digits($lab->min_wpm) }} WPM,
                        {{ \App\Support\Bn::digits($lab->min_accuracy) }}% নির্ভুলতা এবং নির্ধারিত সময়ের
                        কমপক্ষে {{ \App\Support\Bn::digits($lab->min_duration_percent) }}% টাইপ করা।
                        প্রথম অক্ষর টাইপ করলেই টাইমার শুরু হবে।
                    </div>
                    @if (\App\Models\Setting::strictSkip())
                        <div class="rounded-2xl bg-amber-50 border border-amber-200 px-4 py-3 text-xs text-amber-900 leading-relaxed">
                            <b>⚠️ গুরুত্বপূর্ণ নিয়ম:</b> কোনো শব্দ বা লাইন বাদ পড়লে <b>এর পর থেকে সব শব্দ ভুল</b> ধরা হবে এবং গতি গোনা হবে না — সরকারি পরীক্ষার মতোই। তাই লাইন দেখে দেখে, একটাও না ছেড়ে টাইপ করুন।
                        </div>
                    @endif

                    <button class="btn-primary text-lg" :disabled="loading" :class="loading && 'opacity-70'">
                        <span x-show="!loading">GO (শুরু করুন)</span>
                        <span x-show="loading" x-cloak>প্যাসেজ লোড হচ্ছে...</span>
                    </button>
                </form>
            </div>
        </div>
    </main>
@endsection
