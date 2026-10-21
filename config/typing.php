<?php

return [
    // নতুন ইউজারকে কত দিনের ফ্রি সাবস্ক্রিপশন দেওয়া হবে
    'trial_days' => 15,

    // মেয়াদ শেষের কত দিন আগে থেকে "শীঘ্রই শেষ হবে" রিমাইন্ডার দেখাবে (ডিফল্ট ২ দিন)
    'reminder_days' => (int) env('TYPING_REMINDER_DAYS', 2),

    // মেয়াদ শেষের পরেও কত দিন টেস্ট দেওয়া চলবে (গ্রেস পিরিয়ড; 0 = বন্ধ)
    'grace_days' => (int) env('TYPING_GRACE_DAYS', 1),

    // প্রোফাইল ছবির সর্বোচ্চ সাইজ (KB)
    'avatar_max_kb' => 2048,

    // টেস্ট় (মিনিট) — সেটআপ পেজে এগুলোই দেখাবে
    'durations' => [1, 3, 5, 10],

    // রেজাল্ট লিস্টে প্রতি পেজে কয়টি
    'per_page' => 10,

    // 'টাইপিং শুরু' — পরের টায়ার আনলক করতে বর্তমান টায়ারে কয়টা কার্ড পাস করতে হবে
    'practice_unlock_threshold' => 5,

    'languages' => [
        'bangla' => 'বাংলা',
        'english' => 'English',
    ],

    // প্রিমিয়াম একটিভেশন: Dial (USSD) করে টাকা পাঠানোর নম্বর, প্ল্যাটফর্ম অনুযায়ী।
    // প্রোডাকশনে নিজের আসল bKash/Nagad/Rocket নম্বর বসিয়ে দিন (Personal/Agent যা-ই হোক)।
    'payment_numbers' => [
        'bkash' => env('PAYMENT_NUMBER_BKASH', '01700000000'),
        'nagad' => env('PAYMENT_NUMBER_NAGAD', '01700000000'),
        'rocket' => env('PAYMENT_NUMBER_ROCKET', '01700000000'),
    ],
];
