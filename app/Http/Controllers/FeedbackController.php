<?php

namespace App\Http\Controllers;

use App\Models\FeedbackReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FeedbackController extends Controller
{
    public const DIR = 'uploads/feedback';

    /** পেজ অনেকক্ষণ খোলা থাকলে নতুন CSRF টোকেন দেয় */
    public function token(): JsonResponse
    {
        return response()->json(['token' => csrf_token()])
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0');
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:problem,idea,other'],
            'message' => ['required', 'string', 'min:5', 'max:2000'],
            'page_url' => ['nullable', 'string', 'max:500'],
            'screenshot' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
        ], [
            'message.required' => 'সমস্যাটি সংক্ষেপে লিখুন।',
            'message.min' => 'আরেকটু বিস্তারিত লিখুন (কমপক্ষে ৫ অক্ষর)।',
            'message.max' => 'মেসেজ সর্বোচ্চ ২০০০ অক্ষরের হতে পারবে।',
            'screenshot.image' => 'স্ক্রিনশট অবশ্যই ছবি হতে হবে।',
            'screenshot.mimes' => 'শুধু JPG, PNG বা WEBP ছবি দেওয়া যাবে।',
            'screenshot.uploaded' => 'স্ক্রিনশট আপলোড হয়নি — ছবিটি সার্ভারের সীমার চেয়ে বড়। ছোট ছবি দিন বা অ্যাডমিনকে জানান।',
            'screenshot.max' => 'স্ক্রিনশট সর্বোচ্চ ৪ MB হতে পারবে।',
        ]);

        // স্প্যাম ঠেকাতে: প্রতি ইউজার ঘণ্টায় সর্বোচ্চ ১০টি
        $user = $request->user();
        if (FeedbackReport::where('user_id', $user->id)->where('created_at', '>=', now()->subHour())->count() >= 10) {
            return response()->json(['message' => 'অনেকগুলো মেসেজ পাঠানো হয়েছে, একটু পরে আবার চেষ্টা করুন।'], 429);
        }

        $path = null;
        if ($file = $request->file('screenshot')) {
            $name = $user->id.'-'.Str::random(12).'.'.$file->extension();
            $file->move(public_path(self::DIR), $name);
            $path = self::DIR.'/'.$name;
        }

        FeedbackReport::create([
            'user_id' => $user->id,
            'type' => $data['type'],
            'message' => $data['message'],
            'screenshot' => $path,
            'page_url' => Str::limit((string) ($data['page_url'] ?? ''), 500, ''),
            'user_agent' => Str::limit((string) $request->userAgent(), 300, ''),
        ]);

        \App\Models\ActivityLog::record('feedback', 'মেসেজ/সমস্যা জানিয়েছেন ('.(\App\Models\FeedbackReport::TYPES[$data['type']] ?? $data['type']).')', [], $request);

        return response()->json(['ok' => true, 'message' => 'ধন্যবাদ! আপনার মেসেজ অ্যাডমিনের কাছে পৌঁছে গেছে।']);
    }
}
