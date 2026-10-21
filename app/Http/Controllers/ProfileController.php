<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    private const AVATAR_DIR = 'uploads/avatars';

    /** প্রোফাইল: তথ্য, ছবি, পাসওয়ার্ড, সাবস্ক্রিপশনের সারাংশ ও পরিসংখ্যান */
    public function edit(Request $request): View
    {
        $user = $request->user();

        $stats = [
            'attempts' => $user->typingAttempts()->count(),
            'passed' => $user->typingAttempts()->where('result', 'pass')->count(),
            'best_wpm' => (float) $user->typingAttempts()->max('wpm'),
            'avg_accuracy' => (float) $user->typingAttempts()->avg('accuracy'),
        ];

        // সাবস্ক্রিপশনের মেয়াদের অগ্রগতি বার-এর জন্য (শেষ কেনা/পাওয়া প্যাকেজের দৈর্ঘ্যের তুলনায়)
        $lastPurchase = $user->subscriptionPurchases()->latest('id')->first();
        $totalDays = $lastPurchase ? max(1, (int) $lastPurchase->starts_at->diffInDays($lastPurchase->ends_at)) : Setting::typing('trial_days');
        $progress = $user->hasActiveSubscription() ? min(100, (int) round($user->daysLeft() / $totalDays * 100)) : 0;

        $recentWallet = $user->walletTransactions()->latest()->take(5)->get();

        return view('profile.edit', compact('user', 'stats', 'progress', 'recentWallet'));
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'phone' => ['nullable', 'regex:/^01[3-9][0-9]{8}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'institute' => ['nullable', 'string', 'max:120'],
            'district' => ['nullable', 'string', 'max:60'],
        ], [
            'name.required' => 'আপনার নাম দিন।',
            'phone.regex' => 'সঠিক মোবাইল নম্বর দিন (যেমন 01712345678)।',
            'phone.unique' => 'এই মোবাইল নম্বর অন্য একাউন্টে ব্যবহৃত হচ্ছে।',
        ]);

        $user->update($data);

        return redirect()->route('profile.edit')->with('success', 'প্রোফাইল আপডেট হয়েছে।');
    }

    public function uploadAvatar(Request $request): RedirectResponse
    {
        $request->validate([
            'avatar' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:'.(int) config('typing.avatar_max_kb')],
        ], [
            'avatar.required' => 'একটি ছবি বেছে নিন।',
            'avatar.image' => 'ফাইলটি অবশ্যই ছবি হতে হবে।',
            'avatar.mimes' => 'শুধু JPG, PNG বা WEBP ছবি দেওয়া যাবে।',
            'avatar.max' => 'ছবির সাইজ সর্বোচ্চ '.round(config('typing.avatar_max_kb') / 1024, 1).' MB হতে পারবে।',
        ]);

        $user = $request->user();
        $file = $request->file('avatar');
        $name = $user->id.'-'.Str::random(10).'.'.$file->extension();
        $file->move(public_path(self::AVATAR_DIR), $name);

        $this->deleteLocalAvatar($user->avatar);
        $user->update(['avatar' => self::AVATAR_DIR.'/'.$name]);

        return redirect()->route('profile.edit')->with('success', 'প্রোফাইল ছবি আপডেট হয়েছে।');
    }

    public function removeAvatar(Request $request): RedirectResponse
    {
        $user = $request->user();
        $this->deleteLocalAvatar($user->avatar);
        $user->update(['avatar' => null]);

        return redirect()->route('profile.edit')->with('success', 'প্রোফাইল ছবি সরানো হয়েছে।');
    }

    public function updatePassword(Request $request): RedirectResponse
    {
        $user = $request->user();

        // গুগল দিয়ে খোলা একাউন্টে আগে কখনো পাসওয়ার্ড সেট করা না থাকলে বর্তমান পাসওয়ার্ড চাওয়া হয় না
        $rules = [
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ];
        if ($user->hasSetPassword()) {
            $rules['current_password'] = ['required', 'string'];
        }

        $request->validate($rules, [
            'current_password.required' => 'বর্তমান পাসওয়ার্ড দিন।',
            'password.required' => 'নতুন পাসওয়ার্ড দিন।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।',
            'password.confirmed' => 'নতুন পাসওয়ার্ড দুটি মিলছে না।',
        ]);

        if ($user->hasSetPassword() && ! Hash::check($request->input('current_password'), $user->password)) {
            return redirect()->to(route('profile.edit').'#password')
                ->withErrors(['current_password' => 'বর্তমান পাসওয়ার্ড সঠিক নয়।']);
        }

        $user->forceFill(['password' => $request->input('password'), 'password_set_at' => now()])->save();

        return redirect()->to(route('profile.edit').'#password')->with('success', 'পাসওয়ার্ড পরিবর্তন হয়েছে।');
    }

    /** লেনদেনের ইতিহাস: পেমেন্ট আবেদন, সাবস্ক্রিপশন, ওয়ালেট */
    public function transactions(Request $request): View
    {
        $user = $request->user();

        $payments = $user->paymentSubmissions()->with('plan')->latest()->take(50)->get();
        $purchases = $user->subscriptionPurchases()->with('plan')->where('status', '!=', 'cancelled')->latest()->take(50)->get();
        $wallet = $user->walletTransactions()->latest()->take(50)->get();

        return view('profile.transactions', compact('payments', 'purchases', 'wallet'));
    }

    private function deleteLocalAvatar(?string $avatar): void
    {
        if ($avatar && ! str_starts_with($avatar, 'http') && str_starts_with($avatar, self::AVATAR_DIR.'/')) {
            $path = public_path($avatar);
            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}
