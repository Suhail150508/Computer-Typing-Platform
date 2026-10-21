@extends('admin.layouts.app')
@section('title', $plan->exists ? 'প্ল্যান এডিট' : 'নতুন প্ল্যান')

@section('content')
<div class="max-w-2xl mx-auto">
    <a href="{{ route('admin.subscription-plans.index') }}" class="text-sm text-slate-500 hover:text-indigo-600 font-medium">← সব প্ল্যান</a>

    <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 mt-3">
        <h1 class="text-xl font-bold text-slate-800 mb-5">{{ $plan->exists ? 'প্ল্যান এডিট করুন' : 'নতুন প্ল্যান যোগ করুন' }}</h1>

        <form method="POST" action="{{ $plan->exists ? route('admin.subscription-plans.update', $plan) : route('admin.subscription-plans.store') }}" class="space-y-4">
            @csrf
            @if ($plan->exists) @method('PUT') @endif

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">প্ল্যানের নাম *</label>
                <input type="text" name="name" value="{{ old('name', $plan->name) }}" placeholder="যেমন: ৩ মাস প্যাকেজ" required class="w-full rounded-xl border-slate-200 text-sm">
                @error('name') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">বিবরণ (ঐচ্ছিক)</label>
                <textarea name="description" rows="2" class="w-full rounded-xl border-slate-200 text-sm" placeholder="প্ল্যান সম্পর্কে ছোট বিবরণ">{{ old('description', $plan->description) }}</textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">মূল্য (৳) *</label>
                    <input type="number" step="0.01" name="price" value="{{ old('price', $plan->price ?? 0) }}" min="0" required class="w-full rounded-xl border-slate-200 text-sm">
                    @error('price') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">মেয়াদ (দিন) *</label>
                    <input type="number" name="duration_days" value="{{ old('duration_days', $plan->duration_days ?? 30) }}" min="1" required class="w-full rounded-xl border-slate-200 text-sm">
                    @error('duration_days') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">ফিচার (প্রতি লাইনে একটি)</label>
                <textarea name="features_text" rows="4" class="w-full rounded-xl border-slate-200 text-sm font-mono" placeholder="আনলিমিটেড টাইপিং টেস্ট&#10;সব ল্যাব আনলক&#10;প্রায়োরিটি সাপোর্ট">{{ old('features_text', is_array($plan->features) ? implode("\n", $plan->features) : '') }}</textarea>
                <p class="text-xs text-slate-400 mt-1">ইউজারকে দেখানোর জন্য প্ল্যানের সুবিধাগুলো — প্রতিটি লাইন একটি করে পয়েন্ট হিসেবে দেখাবে।</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4 items-end">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">সাজানোর ক্রম</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $plan->sort_order ?? 0) }}" min="0" class="w-full rounded-xl border-slate-200 text-sm">
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold text-slate-600 pb-2">
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', $plan->exists ? $plan->is_active : true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600">
                    সক্রিয় (ইউজাররা কিনতে পারবে)
                </label>
            </div>

            <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold transition">
                {{ $plan->exists ? 'আপডেট করুন' : 'তৈরি করুন' }}
            </button>
        </form>
    </div>
</div>
@endsection
