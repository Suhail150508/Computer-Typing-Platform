@extends('admin.layouts.app')
@section('title', $lab->exists ? 'ল্যাব এডিট' : 'নতুন ল্যাব')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.labs.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium">← সব ল্যাব</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 mt-3">
        <h1 class="text-xl font-bold text-slate-800 mb-5">{{ $lab->exists ? 'ল্যাব এডিট করুন' : 'নতুন ল্যাব যোগ করুন' }}</h1>

        <form method="POST" action="{{ $lab->exists ? route('admin.labs.update', $lab) : route('admin.labs.store') }}" class="space-y-4">
            @csrf
            @if ($lab->exists) @method('PUT') @endif

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">নাম (বাংলা) *</label>
                    <input type="text" name="name_bn" value="{{ old('name_bn', $lab->name_bn) }}" required class="w-full rounded-xl border-slate-200 text-sm">
                    @error('name_bn') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">নাম (English)</label>
                    <input type="text" name="name_en" value="{{ old('name_en', $lab->name_en) }}" class="w-full rounded-xl border-slate-200 text-sm">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ব্যাজ টেক্সট</label>
                    <input type="text" name="badge_text" value="{{ old('badge_text', $lab->badge_text) }}" placeholder="যেমন: বিসিসি ল্যাব" class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">সাবটাইটেল</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle', $lab->subtitle) }}" placeholder="যেমন: শীট ও স্ক্রিন টাইপ" class="w-full rounded-xl border-slate-200 text-sm">
                </div>
            </div>

            <div class="grid grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম WPM *</label>
                    <input type="number" name="min_wpm" value="{{ old('min_wpm', $lab->min_wpm ?: 20) }}" min="1" max="200" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম Accuracy % *</label>
                    <input type="number" name="min_accuracy" value="{{ old('min_accuracy', $lab->min_accuracy ?: 90) }}" min="1" max="100" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ন্যূনতম সময়% *</label>
                    <input type="number" name="min_duration_percent" value="{{ old('min_duration_percent', $lab->min_duration_percent ?: 90) }}" min="1" max="100" required class="w-full rounded-xl border-slate-200 text-sm">
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1.5">⏱ টাইপিংয়ের সময় — ইউজার যেগুলো থেকে বেছে নেবে</label>
                @include('admin.partials.duration-picker', ['mode' => 'multi', 'name' => 'durations', 'value' => old('durations', $lab->durations)])
                @error('durations') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div class="grid sm:grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">সাজানোর ক্রম</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $lab->sort_order ?? 0) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-600 pb-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $lab->exists ? $lab->is_active : true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                    সক্রিয় (ড্যাশবোর্ডে দেখাবে)
                </label>
            </div>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold transition">
                {{ $lab->exists ? 'আপডেট করুন' : 'তৈরি করুন' }}
            </button>
        </form>
    </div>
</div>
@endsection
