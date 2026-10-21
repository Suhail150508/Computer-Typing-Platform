@extends('admin.layouts.app')
@section('title', 'সেটিংস')

@section('content')
<div class="max-w-2xl mx-auto">
    <h1 class="text-2xl font-bold text-slate-800 mb-1">সেটিংস</h1>
    <p class="text-sm text-slate-500 mb-5">পেমেন্ট নম্বর এবং টাইপিং/সাবস্ক্রিপশনের নিয়ম এখান থেকেই বদলানো যায় — কোড বা <code>.env</code> ছুঁতে হবে না। পরিবর্তন সাথে সাথে কার্যকর হয়।</p>

    <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-5">
        @csrf
        @method('PUT')

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-5">
            <h2 class="font-bold text-slate-700">🎯 টাইপিং ও সাবস্ক্রিপশনের নিয়ম</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                @foreach ([
                    ['practice_unlock_threshold', 'পরের ধাপ আনলকে কার্ড পাস', 'টি', 'টাইপিং শুরুে বেসিক/অ্যাডভান্সের এতটি কার্ড পাস করলে পরের ধাপ খুলবে (১–৫০)।'],
                    ['trial_days', 'নতুন ইউজারের ফ্রি ট্রায়াল', 'দিন', 'রেজিস্ট্রেশন বা প্রথম Google লগইনে এই কয় দিন ফ্রি। শুধু নতুনদের জন্য প্রযোজ্য (০–৩৬৫)।'],
                    ['grace_days', 'গ্রেস পিরিয়ড', 'দিন', 'মেয়াদ শেষের পরও এই কয় দিন টেস্ট দেওয়া চলবে। ০ = বন্ধ (০–৩০)।'],
                    ['reminder_days', 'শীঘ্রই শেষ — রিমাইন্ডার', 'দিন', 'মেয়াদ শেষের এত দিন আগে থেকে রিনিউ রিমাইন্ডার দেখাবে — ডিফল্ট ২ দিন (১–৬০)।'],
                ] as [$name, $label, $unit, $hint])
                    <div>
                        <label class="block text-xs font-semibold text-slate-500 mb-1">{{ $label }}</label>
                        <div class="flex items-center gap-2">
                            <input type="number" name="{{ $name }}" value="{{ old($name, $values[$name]) }}" required class="w-full rounded-xl border-slate-200 text-sm">
                            <span class="text-sm text-slate-400 shrink-0">{{ $unit }}</span>
                        </div>
                        <p class="text-[11px] text-slate-400 mt-1">{{ $hint }}</p>
                        @error($name) <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                @endforeach
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-5">
            <h2 class="font-bold text-slate-700">💳 পেমেন্ট নম্বর</h2>
            <p class="text-xs text-slate-500 -mt-3">এই নম্বরগুলো ইউজাররা "প্রিমিয়াম" পেজে টাকা পাঠানোর জন্য দেখবে।</p>

            <div class="grid sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">🩷 bKash নম্বর</label>
                    <input type="text" name="payment_number_bkash" value="{{ old('payment_number_bkash', $values['payment_number_bkash']) }}" placeholder="01XXXXXXXXX" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('payment_number_bkash') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">🧡 Nagad নম্বর</label>
                    <input type="text" name="payment_number_nagad" value="{{ old('payment_number_nagad', $values['payment_number_nagad']) }}" placeholder="01XXXXXXXXX" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('payment_number_nagad') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-xs font-semibold text-slate-500 mb-1">💜 Rocket নম্বর</label>
                    <input type="text" name="payment_number_rocket" value="{{ old('payment_number_rocket', $values['payment_number_rocket']) }}" placeholder="01XXXXXXXXX" class="w-full rounded-xl border-slate-200 text-sm">
                    @error('payment_number_rocket') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-slate-500 mb-1">অতিরিক্ত নির্দেশনা (ঐচ্ছিক)</label>
                <textarea name="payment_instructions" rows="3" class="w-full rounded-xl border-slate-200 text-sm" placeholder="যেমন: Personal নম্বরে Send Money করুন, Payment অপশন ব্যবহার করবেন না।">{{ old('payment_instructions', $values['payment_instructions']) }}</textarea>
                <p class="text-xs text-slate-400 mt-1">প্রিমিয়াম পেজে পেমেন্ট ধাপের নিচে অতিরিক্ত নোট হিসেবে দেখাবে।</p>
            </div>

        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-3">
            <h2 class="font-bold text-slate-700">🎬 হাত বসানোর ভিডিও লিংক</h2>
            <p class="text-xs text-slate-500">ইউজাররা "▶ ভিডিও দেখুন" বাটনে ক্লিক করলে এই লিংক নতুন ট্যাবে খুলবে (যেমন YouTube)। ফাঁকা রাখলে বাটনটি দেখাবে না।</p>
            <input type="url" name="guide_video_url" value="{{ old('guide_video_url', $values['guide_video_url']) }}" placeholder="https://www.youtube.com/watch?v=..." class="w-full rounded-xl border-slate-200 text-sm">
            @error('guide_video_url') <p class="text-rose-500 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="bg-white rounded-2xl border border-slate-200 p-5 sm:p-6 space-y-3">
            <h2 class="font-bold text-slate-700">⚠️ শব্দ/লাইন বাদ পড়লে কড়া নিয়ম</h2>
            <p class="text-xs text-slate-500 leading-relaxed">চালু থাকলে: টাইপ করার সময় কোনো শব্দ বা লাইন বাদ পড়লে (বা বাড়তি শব্দ ঢুকে গেলে) এর পর থেকে টাইপ করা সব শব্দ ভুল ধরা হয় এবং সেগুলোর গতি গোনা হয় না — সরকারি পরীক্ষার মতো। একটা সাধারণ বানান ভুলে শুধু ওই একটি শব্দ ভুল হয়। বন্ধ করলে আগের নরম নিয়ম (বাদ পড়া শব্দ একটি ভুল, বাকি ঠিক থাকে)। নতুন নিয়ম শুধু এরপর থেকে দেওয়া টেস্টে কার্যকর হবে; আগের রেজাল্ট বদলায় না।</p>
            <input type="hidden" name="strict_skip" value="0">
            <label class="flex items-center gap-3 text-sm font-semibold text-slate-700 cursor-pointer">
                <input type="checkbox" name="strict_skip" value="1" @checked(old('strict_skip', $values['strict_skip'] ? '1' : '0') === '1') class="rounded border-slate-300 text-indigo-600 w-5 h-5">
                কড়া নিয়ম চালু (সুপারিশকৃত)
            </label>
        </div>

        <button class="w-full bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl py-3 text-sm font-semibold transition">সংরক্ষণ করুন</button>
    </form>

    <div class="mt-4 rounded-2xl bg-amber-50 border border-amber-100 p-4 text-xs text-amber-700 leading-relaxed">
        <b>মনে রাখুন:</b> এটি একটি ম্যানুয়াল ভেরিফিকেশন সিস্টেম — ইউজার টাকা পাঠানোর পর ট্রানজেকশন আইডি জমা দেবে, আপনাকে <a href="{{ route('admin.payments.index') }}" class="underline font-semibold">পেমেন্ট রিকোয়েস্ট</a> পেজে গিয়ে মোবাইলে টাকা পাওয়ার কথা নিশ্চিত করে অনুমোদন করতে হবে।
    </div>
</div>
@endsection
