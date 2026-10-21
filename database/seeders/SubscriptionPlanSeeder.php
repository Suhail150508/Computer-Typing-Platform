<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $common = [
            'সীমাহীন টাইপিং টেস্ট (সব ল্যাব)',
            'টাইপিং শুরু — বেসিক, অ্যাডভান্স, হার্ড সব কার্ড',
            'লাইভ WPM ট্র্যাকিং ও স্মার্ট চেকার',
        ];

        $plans = [
            [
                'name' => '১ মাস', 'slug' => '1-month', 'duration_days' => 30, 'price' => 99,
                'description' => 'শুরু করার জন্য সবচেয়ে সহজ প্যাকেজ।', 'sort_order' => 1,
                'features' => $common,
            ],
            [
                'name' => '৩ মাস', 'slug' => '3-month', 'duration_days' => 90, 'price' => 249,
                'description' => 'নিয়মিত অনুশীলনকারীদের জন্য জনপ্রিয় পছন্দ।', 'sort_order' => 2,
                'features' => [...$common, '১ মাসের তুলনায় সাশ্রয়ী'],
            ],
            [
                'name' => '৬ মাস', 'slug' => '6-month', 'duration_days' => 180, 'price' => 449,
                'description' => 'দীর্ঘমেয়াদে দক্ষতা গড়ে তুলতে সেরা ব্যালেন্স।', 'sort_order' => 3,
                'features' => [...$common, 'অগ্রাধিকার সাপোর্ট'],
            ],
            [
                'name' => '১ বছর', 'slug' => '12-month', 'duration_days' => 365, 'price' => 799,
                'description' => 'সর্বোচ্চ সাশ্রয় — পুরো বছর নিশ্চিন্তে অনুশীলন করুন।', 'sort_order' => 4,
                'features' => [...$common, 'সর্বোচ্চ মূল্যছাড়', 'অগ্রাধিকার সাপোর্ট'],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(['slug' => $plan['slug']], $plan + ['is_active' => true]);
        }
    }
}
