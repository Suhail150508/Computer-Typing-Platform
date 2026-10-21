<?php

namespace App\Http\Controllers\Auth;

use App\Models\Setting;
use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\Landing;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Laravel\Socialite\Facades\Socialite;

class AuthController extends Controller
{
    public function showLogin(): View
    {
        return view('auth.login');
    }

    public function login(Request $request): RedirectResponse
    {
        // লগইন ফর্মের ফিল্ডের নাম যা-ই হোক (identity / login / email / phone) — যেটাতেই ইমেইল বা মোবাইল আসুক, ধরে নেবে
        $request->merge(['login' => trim((string) ($request->input('identity') ?: $request->input('login') ?: $request->input('email') ?: $request->input('phone')))]);

        $data = $request->validate([
            'login' => ['required', 'string', 'max:255'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'ইমেইল বা মোবাইল নম্বর দিন।',
            'password.required' => 'পাসওয়ার্ড দিন।',
        ]);

        $field = filter_var($data['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'phone';

        $ok = Auth::attempt(
            [$field => $data['login'], 'password' => $data['password'], 'is_active' => true],
            $request->boolean('remember')
        );

        if (! $ok) {
            // পাসওয়ার্ড ঠিক কিন্তু একাউন্ট বন্ধ থাকলে আলাদা, পরিষ্কার মেসেজ (মেয়াদ শেষ হলে কিন্তু লগইন আটকায় না)
            $blocked = User::where($field, $data['login'])->where('is_active', false)->first();
            if ($blocked && Hash::check($data['password'], $blocked->password)) {
                return back()->withInput($request->only('login'))
                    ->withErrors(['login' => $this->inactiveMessage($blocked)]);
            }

            return back()->withInput($request->only('login'))
                ->withErrors(['login' => 'ইমেইল/মোবাইল বা পাসওয়ার্ড সঠিক নয়।']);
        }

        $request->session()->regenerate();
        $request->user()->forceFill(['last_login_at' => now()])->save();

        return $this->afterLogin($request, $request->user());
    }

    /**
     * লগইনের পর গন্তব্য: আগে থেকে দেখতে চাওয়া নির্দিষ্ট পেজ (যেমন একটা রেজাল্ট লিংক) থাকলে সেখানে,
     * কিন্তু শুধু ড্যাশবোর্ড/হোম হলে তা উপেক্ষা করে স্মার্ট ল্যান্ডিং (নতুন ইউজার → টাইপিং শুরু)।
     */
    private function afterLogin(Request $request, User $user): RedirectResponse
    {
        $intended = $request->session()->pull('url.intended');
        $generic = [route('dashboard'), url('/')];

        if ($intended && ! $user->isAdmin() && ! in_array(rtrim($intended, '/'), array_map(fn ($u) => rtrim($u, '/'), $generic), true)) {
            return redirect()->to($intended);
        }

        return redirect()->to(Landing::url($user));
    }

    private function inactiveMessage(User $user): string
    {
        $msg = 'আপনার একাউন্টটি বর্তমানে বন্ধ আছে।';
        if (filled($user->deactivated_reason)) {
            $msg .= ' কারণ: '.$user->deactivated_reason.'।';
        }

        return $msg.' অনুগ্রহ করে অ্যাডমিনের সাথে যোগাযোগ করুন।';
    }

    public function showRegister(): View
    {
        return view('auth.register');
    }

    public function register(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'regex:/^01[3-9][0-9]{8}$/', 'unique:users,phone'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required' => 'আপনার নাম দিন।',
            'email.required' => 'ইমেইল দিন।',
            'email.email' => 'সঠিক ইমেইল দিন।',
            'email.unique' => 'এই ইমেইল দিয়ে আগেই একাউন্ট আছে।',
            'phone.regex' => 'সঠিক মোবাইল নম্বর দিন (যেমন 01712345678)।',
            'phone.unique' => 'এই মোবাইল নম্বর দিয়ে আগেই একাউন্ট আছে।',
            'password.min' => 'পাসওয়ার্ড কমপক্ষে ৬ অক্ষরের হতে হবে।',
            'password.confirmed' => 'পাসওয়ার্ড দুটি মিলছে না।',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'] ?? null,
            'password' => $data['password'],
            'password_set_at' => now(),
            'subscription_expires_at' => now()->addDays(Setting::typing('trial_days')),
        ]);

        Auth::login($user);
        $request->session()->regenerate();
        \App\Models\ActivityLog::record('register', "নতুন রেজিস্ট্রেশন: {$user->name}", [], $request, $user->id);

        return redirect()->to(Landing::url($user))
            ->with('success', 'স্বাগতম! আপনাকে '.Setting::typing('trial_days').' দিনের ফ্রি ট্রায়াল দেওয়া হয়েছে।');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }

    /** গুগলের লগইন পেজে পাঠানো হয় */
    public function googleRedirect(): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()->route('login')->with(
                'error',
                'Google লগইন এখনো কনফিগার করা হয়নি। অ্যাডমিনকে .env ফাইলে GOOGLE_CLIENT_ID ও GOOGLE_CLIENT_SECRET বসাতে বলুন (README দেখুন)।'
            );
        }

        return Socialite::driver('google')->redirect();
    }

    /** গুগল থেকে ফিরে এসে এখানে ইউজার লগইন/তৈরি হয় */
    public function googleCallback(Request $request): RedirectResponse
    {
        if (blank(config('services.google.client_id')) || blank(config('services.google.client_secret'))) {
            return redirect()->route('login')->with('error', 'Google লগইন এখনো কনফিগার করা হয়নি।');
        }

        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Throwable $e) {
            return redirect()->route('login')->withErrors(['login' => 'গুগল দিয়ে লগইন সম্পন্ন করা যায়নি, আবার চেষ্টা করুন।']);
        }

        $email = $googleUser->getEmail();
        if (blank($email)) {
            return redirect()->route('login')->withErrors(['login' => 'আপনার গুগল একাউন্টে ইমেইল পাওয়া যায়নি।']);
        }

        $user = User::where('email', $email)->first();

        if (! $user) {
            $isNewGoogle = true;
            $user = User::create([
                'name' => $googleUser->getName() ?: $googleUser->getNickname() ?: explode('@', $email)[0],
                'email' => $email,
                'google_id' => $googleUser->getId(),
                'avatar' => $googleUser->getAvatar(),
                'password' => Hash::make(Str::random(40)),
                'email_verified_at' => now(),
                'subscription_expires_at' => now()->addDays(Setting::typing('trial_days')),
            ]);
        } elseif (! $user->google_id) {
            // আগে ইমেইল/পাসওয়ার্ড দিয়ে একাউন্ট খোলা থাকলে এখন গুগলের সাথে লিংক করে দেওয়া হয়
            $user->forceFill([
                'google_id' => $googleUser->getId(),
                'avatar' => $user->avatar ?: $googleUser->getAvatar(),
            ])->save();
        }

        if (! $user->is_active) {
            return redirect()->route('login')->withErrors(['login' => $this->inactiveMessage($user)]);
        }

        Auth::login($user, true);
        $request->session()->regenerate();
        $user->forceFill(['last_login_at' => now()])->save();
        if (! empty($isNewGoogle)) {
            \App\Models\ActivityLog::record('register', "নতুন রেজিস্ট্রেশন (গুগল): {$user->name}", [], $request, $user->id);
        }

        return $this->afterLogin($request, $user);
    }
}
