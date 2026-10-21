<?php

namespace App\Support;

use App\Models\User;

/**
 * লগইনের পর ইউজার কোথায় নামবে — একটি জায়গায় রাখা, যাতে সাধারণ লগইন ও গুগল লগইন একই নিয়ম মানে।
 *
 *  - অ্যাডমিন            → অ্যাডমিন ড্যাশবোর্ড
 *  - মেয়াদ শেষ           → প্রিমিয়াম/রিনিউ পেজ (লগইন কখনো আটকায় না!)
 *  - নতুন বা প্র্যাকটিস-ইউজার → টাইপিং শুরু (সর্বশেষ ভাষায়)
 *  - ল্যাব-টেস্ট ইউজার      → টাইপিং ল্যাব (ড্যাশবোর্ড)
 */
class Landing
{
    public static function url(User $user): string
    {
        if ($user->isAdmin()) {
            return route('admin.dashboard');
        }

        if (! $user->canPractice()) {
            return route('premium.index');
        }

        $last = $user->typingAttempts()->latest('id')->first(['id', 'source', 'language']);

        if ($last === null) {
            return route('practice.index');
        }

        return $last->source === 'practice'
            ? route('practice.index', ['lang' => $last->language === 'english' ? 'english' : 'bangla'])
            : route('dashboard');
    }
}
