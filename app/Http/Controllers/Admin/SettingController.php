<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function edit(): View
    {
        $values = [
            'payment_number_bkash' => Setting::get('payment_number_bkash', ''),
            'payment_number_nagad' => Setting::get('payment_number_nagad', ''),
            'payment_number_rocket' => Setting::get('payment_number_rocket', ''),
            'payment_instructions' => Setting::get('payment_instructions', ''),
            'guide_video_url' => Setting::get('guide_video_url', ''),
            'strict_skip' => Setting::strictSkip(),
        ];

        // টাইপিং ও সাবস্ক্রিপশনের নিয়ম (বর্তমানে কার্যকর মান — অ্যাডমিন সেট না করলে ডিফল্ট)
        foreach (array_keys(Setting::TYPING_RULES) as $key) {
            $values[$key] = Setting::typing($key);
        }

        return view('admin.settings.edit', compact('values'));
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'payment_number_bkash' => ['nullable', 'string', 'max:20'],
            'payment_number_nagad' => ['nullable', 'string', 'max:20'],
            'payment_number_rocket' => ['nullable', 'string', 'max:20'],
            'payment_instructions' => ['nullable', 'string', 'max:1000'],
            'guide_video_url' => ['nullable', 'string', 'max:500', 'regex:#^https?://#i'],
            'strict_skip' => ['nullable', 'in:0,1'],
            'practice_unlock_threshold' => ['required', 'integer', 'between:1,50'],
            'trial_days' => ['required', 'integer', 'between:0,365'],
            'grace_days' => ['required', 'integer', 'between:0,30'],
            'reminder_days' => ['required', 'integer', 'between:1,60'],
        ], [
            'practice_unlock_threshold.*' => 'আনলকের জন্য কার্ড সংখ্যা ১ থেকে ৫০ এর মধ্যে হতে হবে।',
            'trial_days.*' => 'ফ্রি ট্রায়ালের দিন ০ থেকে ৩৬৫ এর মধ্যে হতে হবে।',
            'grace_days.*' => 'গ্রেস পিরিয়ড ০ থেকে ৩০ দিনের মধ্যে হতে হবে।',
            'guide_video_url.regex' => 'ভিডিওর লিংক অবশ্যই http:// বা https:// দিয়ে শুরু হতে হবে।',
            'reminder_days.*' => 'রিমাইন্ডারের দিন ১ থেকে ৬০ এর মধ্যে হতে হবে।',
        ]);

        foreach ($data as $key => $value) {
            Setting::set(array_key_exists($key, Setting::TYPING_RULES) ? 'typing_'.$key : $key, $value ?? '');
        }

        return back()->with('success', 'সেটিংস সংরক্ষণ হয়েছে।');
    }
}
