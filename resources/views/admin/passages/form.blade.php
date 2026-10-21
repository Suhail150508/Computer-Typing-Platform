@extends('admin.layouts.app')
@section('title', $passage->exists ? 'প্যাসেজ এডিট' : 'নতুন প্যাসেজ')

@section('content')
<div class="max-w-3xl mx-auto">
    <a href="{{ route('admin.passages.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium">← সব প্যাসেজ</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 mt-3">
        <h1 class="text-xl font-bold text-slate-800 mb-5">{{ $passage->exists ? 'প্যাসেজ এডিট করুন' : 'নতুন প্যাসেজ যোগ করুন' }}</h1>

        <form method="POST" action="{{ $passage->exists ? route('admin.passages.update', $passage) : route('admin.passages.store') }}" class="space-y-4">
            @csrf
            @if ($passage->exists) @method('PUT') @endif

            <div class="grid sm:grid-cols-3 gap-4">
                <div class="sm:col-span-2">
                    <label class="block text-xs font-semibold text-slate-500 mb-1">শিরোনাম (ঐচ্ছিক)</label>
                    <input type="text" name="title" value="{{ old('title', $passage->title) }}" class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">ল্যাব *</label>
                    <select name="lab_id" required class="w-full rounded-xl border-slate-200 text-sm">
                        <option value="">বেছে নিন</option>
                        @foreach ($labs as $l)
                            <option value="{{ $l->id }}" @selected(old('lab_id', $passage->lab_id) == $l->id)>{{ $l->name_bn }}</option>
                        @endforeach
                    </select>
                    @error('lab_id') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ভাষা *</label>
                <div class="flex gap-4">
                    <label class="flex items-center gap-1.5 text-sm"><input type="radio" name="language" value="bangla" {{ old('language', $passage->language ?? 'bangla') === 'bangla' ? 'checked' : '' }}> বাংলা</label>
                    <label class="flex items-center gap-1.5 text-sm"><input type="radio" name="language" value="english" {{ old('language', $passage->language ?? '') === 'english' ? 'checked' : '' }}> English</label>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">কনটেন্ট * <span class="font-normal text-slate-400">(একাধিক অনুচ্ছেদ আলাদা করতে ফাঁকা লাইন দিন)</span></label>
                <textarea name="content" rows="10" required class="w-full rounded-xl border-slate-200 text-sm font-mono leading-relaxed">{{ old('content', $passage->content) }}</textarea>
                @error('content') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
                <input type="checkbox" name="is_active" value="1" {{ old('is_active', $passage->exists ? $passage->is_active : true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                সক্রিয় (ইউজাররা টেস্টে পাবে)
            </label>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold transition">
                {{ $passage->exists ? 'আপডেট করুন' : 'তৈরি করুন' }}
            </button>
        </form>
    </div>
</div>
@endsection
