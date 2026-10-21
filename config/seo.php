<?php

return [
    // সাইটের নাম — টাইটেলের শেষে " | " দিয়ে জুড়ে যায়
    'site_name' => env('APP_NAME', 'TypingMoja'),

    // কোনো পেজ নিজের মেটা ডেসক্রিপশন না দিলে এটাই ব্যবহার হবে
    'default_description' => 'বাংলা ও ইংরেজি টাইপিং স্পিড ও নির্ভুলতা বাড়ানোর সম্পূর্ণ প্ল্যাটফর্ম। বেসিক থেকে এক্সপার্ট — ধাপে ধাপে অনুশীলন করুন, লাইভ WPM ট্র্যাক করুন, একদম ফ্রিতে শুরু করুন।',

    // সাধারণ কিছু কীওয়ার্ড (ঐচ্ছিক, আধুনিক সার্চ ইঞ্জিন এটাকে কম গুরুত্ব দেয় কিন্তু ক্ষতি করে না)
    'default_keywords' => 'বাংলা টাইপিং, টাইপিং স্পিড টেস্ট, টাইপিং প্র্যাকটিস, বাংলা টাইপিং শেখা, WPM টেস্ট, bangla typing, typing speed test bangla, typing practice',

    // শেয়ার করলে যে ছবিটা দেখাবে (Facebook/WhatsApp/Twitter প্রিভিউ) — একটা 1200x630px ছবি বানিয়ে
    // public/images/og-cover.png এ রাখুন, তারপর নিচের লাইনটা আনকমেন্ট করুন।
    'og_image' => env('SEO_OG_IMAGE', null), // যেমন: '/images/og-cover.png'

    // টুইটার হ্যান্ডেল থাকলে (ঐচ্ছিক)
    'twitter_handle' => env('SEO_TWITTER_HANDLE', null), // যেমন: '@typingbd'

    // Google Search Console ভেরিফিকেশন মেটা-ট্যাগ (সাইট ভেরিফাই করতে Search Console থেকে কোডটা বসান)
    'google_site_verification' => env('SEO_GOOGLE_VERIFICATION', null),
];
