<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PasswordResetController extends Controller
{
    private const SENT = 'যদি এই ইমেইল দিয়ে কোনো একাউন্ট থাকে, তাহলে পাসওয়ার্ড রিসেটের লিংক পাঠানো হয়েছে। ইনবক্স (ও স্প্যাম ফোল্ডার) দেখুন।';

    public function request(): View
    {
        return view('auth.forgot-password');
    }

    public function email(Request $request): RedirectResponse
    {
        $data = $request->validate(['email' => ['required', 'email', 'max:255']], [
            'email.required' => 'আপনার ইমেইল দিন।',
            'email.email' => 'সঠিক ইমেইল দিন।',
        ]);

        try {
            $status = Password::sendResetLink(['email' => trim($data['email'])]);
        } catch (\Throwable $e) {
            // মেইল সার্ভার ঠিকমতো সেট না থাকলে ইউজারকে ৫০০ পেজ না দেখিয়ে লগে লিখে রাখা হয়
            Log::error('Password reset mail failed: '.$e->getMessage());

            return back()->withInput()->withErrors(['email' => 'ইমেইল পাঠানো যায়নি। কিছুক্ষণ পর আবার চেষ্টা করুন, না হলে অ্যাডমিনকে জানান।']);
        }

        if ($status === Password::RESET_THROTTLED) {
            return back()->withInput()->withErrors(['email' => 'একটু আগেই লিংক পাঠানো হয়েছে। অনুগ্রহ করে ১ মিনিট পর আবার চেষ্টা করুন।']);
        }

        // একাউন্ট আছে কি না — তা বুঝতে দেওয়া হয় না (নিরাপত্তার জন্য)
        return back()->with('status', self::SENT);
    }

    public function form(Request $request, string $token): View
    {
        return view('auth.reset-password', [
            'token' => $token,
            'email' => (string) $request->query('email', ''),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'token' => ['required', 'string'],
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'email.required' => 'ইমেইল দিন।',
            'email.email' => 'সঠিক ইমেইল দিন।',
            'password.required' => 'নতুন পাসওয়ার্ড দিন।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড দুটি মিলছে না।',
        ]);

        $status = Password::reset(
            ['email' => trim($data['email']), 'password' => $data['password'], 'password_confirmation' => $request->input('password_confirmation'), 'token' => $data['token']],
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => $password, // মডেলের 'hashed' cast নিজেই হ্যাশ করে
                    'password_set_at' => now(),
                    'remember_token' => Str::random(60),
                ])->save();

                event(new PasswordReset($user));
            }
        );

        if ($status === Password::PASSWORD_RESET) {
            return redirect()->route('login')->with('status', 'পাসওয়ার্ড বদলানো হয়েছে। নতুন পাসওয়ার্ড দিয়ে লগইন করুন।');
        }

        $msg = match ($status) {
            Password::INVALID_TOKEN => 'লিংকটির মেয়াদ শেষ হয়েছে বা আগেই ব্যবহার করা হয়েছে। নতুন করে লিংক চেয়ে নিন।',
            Password::INVALID_USER => 'এই ইমেইল দিয়ে কোনো একাউন্ট পাওয়া যায়নি।',
            default => 'পাসওয়ার্ড বদলানো যায়নি, আবার চেষ্টা করুন।',
        };

        return back()->withInput($request->only('email'))->withErrors(['email' => $msg]);
    }
}
