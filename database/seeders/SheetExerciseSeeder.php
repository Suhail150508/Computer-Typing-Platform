<?php

namespace Database\Seeders;

use App\Models\SheetExercise;
use Illuminate\Database\Seeder;

/** শীট টাইপিংয়ের ডেমো অনুশীলন — updateOrCreate, তাই বারবার চালালে ডুপ্লিকেট হয় না (অ্যাডমিনে যোগ করা অন্য শীট অক্ষত থাকে) */
class SheetExerciseSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->sheets() as $i => $s) {
            SheetExercise::updateOrCreate(
                ['title' => $s['title'], 'language' => $s['language']],
                [
                    'description' => $s['description'],
                    'icon' => $s['icon'],
                    'order' => $s['order'],
                    'headers' => $s['headers'],
                    'rows' => $s['rows'],
                    'duration_seconds' => $s['duration'],
                    'min_wpm' => $s['wpm'],
                    'min_accuracy' => $s['acc'],
                    'min_duration_percent' => 60,
                    'is_active' => true,
                ]
            );
        }
    }

    /** @return list<array<string, mixed>> */
    private function sheets(): array
    {
        return [
            [
                'title' => 'শিক্ষার্থীর নম্বরপত্র', 'language' => 'bangla', 'icon' => '🎓', 'order' => 1, 'duration' => 180, 'wpm' => 10, 'acc' => 85,
                'description' => 'নাম, রোল ও বিষয়ভিত্তিক নম্বর — সহজ শুরু।',
                'headers' => ['নাম', 'রোল', 'বাংলা', 'ইংরেজি', 'গণিত'],
                'rows' => [
                    ['রহিম উদ্দিন', '১০১', '৭৮', '৮২', '৯০'],
                    ['করিম মিয়া', '১০২', '৬৫', '৭০', '৭৫'],
                    ['ফাতেমা বেগম', '১০৩', '৮৮', '৯১', '৮৫'],
                    ['সুমাইয়া আক্তার', '১০৪', '৯২', '৮৭', '৯৫'],
                    ['তানভীর আহমেদ', '১০৫', '৭২', '৬৮', '৮০'],
                    ['নুসরাত জাহান', '১০৬', '৮১', '৭৯', '৮৮'],
                    ['মাহমুদুল হাসান', '১০৭', '৬০', '৭৪', '৭০'],
                    ['শারমিন সুলতানা', '১০৮', '৯০', '৮৫', '৯২'],
                ],
            ],
            [
                'title' => 'কর্মচারীর বেতন তালিকা', 'language' => 'bangla', 'icon' => '💼', 'order' => 2, 'duration' => 300, 'wpm' => 15, 'acc' => 88,
                'description' => 'পদবি, মূল বেতন ও ভাতার হিসাব — অফিস অপারেটরের চাকরির পরীক্ষার ধাঁচে।',
                'headers' => ['ক্রমিক', 'কর্মচারীর নাম', 'পদবি', 'মূল বেতন', 'বাড়িভাড়া', 'মোট'],
                'rows' => [
                    ['১', 'আব্দুল কাদের', 'উচ্চমান সহকারী', '২২০০০', '১১০০০', '৩৩০০০'],
                    ['২', 'নাজমা খাতুন', 'হিসাবরক্ষক', '২৫০০০', '১২৫০০', '৩৭৫০০'],
                    ['৩', 'শফিকুল ইসলাম', 'অফিস সহায়ক', '১৬০০০', '৮০০০', '২৪০০০'],
                    ['৪', 'রোকেয়া সুলতানা', 'কম্পিউটার অপারেটর', '২০০০০', '১০০০০', '৩০০০০'],
                    ['৫', 'জাহিদ হোসেন', 'ক্যাশিয়ার', '২৮০০০', '১৪০০০', '৪২০০০'],
                    ['৬', 'মরিয়ম আক্তার', 'সহকারী পরিচালক', '৩৫০০০', '১৭৫০০', '৫২৫০০'],
                    ['৭', 'ইকবাল আহমেদ', 'প্রধান সহকারী', '৩০০০০', '১৫০০০', '৪৫০০০'],
                    ['৮', 'সালমা বেগম', 'স্টেনোগ্রাফার', '২৪০০০', '১২০০০', '৩৬০০০'],
                    ['৯', 'মনিরুল হক', 'ড্রাইভার', '১৮০০০', '৯০০০', '২৭০০০'],
                    ['১০', 'পারভীন আক্তার', 'রিসেপশনিস্ট', '১৯০০০', '৯৫০০', '২৮৫০০'],
                ],
            ],
            [
                'title' => 'জেলার তথ্যসারণি', 'language' => 'bangla', 'icon' => '🗺️', 'order' => 3, 'duration' => 300, 'wpm' => 15, 'acc' => 88,
                'description' => 'বিভাগ, জেলা ও জনসংখ্যার তথ্য — বাংলা যুক্তাক্ষর অনুশীলনের জন্য।',
                'headers' => ['বিভাগ', 'জেলা', 'উপজেলা', 'জনসংখ্যা (লক্ষ)'],
                'rows' => [
                    ['ঢাকা', 'গাজীপুর', 'কালিয়াকৈর', '২১.৪'],
                    ['চট্টগ্রাম', 'কক্সবাজার', 'রামু', '২৫.৯'],
                    ['রাজশাহী', 'বগুড়া', 'শেরপুর', '৩৪.০'],
                    ['খুলনা', 'যশোর', 'অভয়নগর', '২৯.২'],
                    ['বরিশাল', 'পটুয়াখালী', 'কলাপাড়া', '১৫.৩'],
                    ['সিলেট', 'মৌলভীবাজার', 'শ্রীমঙ্গল', '২০.১'],
                    ['রংপুর', 'দিনাজপুর', 'বীরগঞ্জ', '২৯.১'],
                    ['ময়মনসিংহ', 'নেত্রকোনা', 'দুর্গাপুর', '২২.৮'],
                ],
            ],
            [
                'title' => 'Product Price List', 'language' => 'english', 'icon' => '🛒', 'order' => 1, 'duration' => 180, 'wpm' => 15, 'acc' => 88,
                'description' => 'Item names, quantities and prices — a classic data-entry sheet.',
                'headers' => ['Item', 'Code', 'Qty', 'Unit Price', 'Total'],
                'rows' => [
                    ['Wireless Mouse', 'WM-101', '12', '450', '5400'],
                    ['USB Keyboard', 'KB-205', '8', '650', '5200'],
                    ['HDMI Cable', 'HC-310', '20', '180', '3600'],
                    ['Laptop Stand', 'LS-412', '6', '1200', '7200'],
                    ['Webcam HD', 'WC-515', '5', '1800', '9000'],
                    ['Power Strip', 'PS-620', '15', '520', '7800'],
                    ['Flash Drive 64GB', 'FD-733', '30', '550', '16500'],
                    ['Monitor 22 inch', 'MN-840', '4', '11500', '46000'],
                ],
            ],
            [
                'title' => 'Employee Directory', 'language' => 'english', 'icon' => '📇', 'order' => 2, 'duration' => 300, 'wpm' => 20, 'acc' => 90,
                'description' => 'Names, departments, cities and contact numbers.',
                'headers' => ['Name', 'Department', 'City', 'Phone', 'Joined'],
                'rows' => [
                    ['Michael Anderson', 'Accounts', 'Dhaka', '01711223344', '2019-03-12'],
                    ['Sarah Johnson', 'Marketing', 'Chattogram', '01822334455', '2020-07-01'],
                    ['David Thompson', 'IT Support', 'Khulna', '01933445566', '2018-11-23'],
                    ['Emily Roberts', 'Human Resources', 'Rajshahi', '01644556677', '2021-02-15'],
                    ['Daniel Harris', 'Sales', 'Sylhet', '01755667788', '2017-09-30'],
                    ['Olivia Martin', 'Procurement', 'Barishal', '01866778899', '2022-05-04'],
                    ['James Wilson', 'Operations', 'Rangpur', '01977889900', '2016-12-19'],
                    ['Sophia Clark', 'Legal Affairs', 'Mymensingh', '01688990011', '2023-01-09'],
                    ['William Turner', 'Logistics', 'Comilla', '01799001122', '2019-08-27'],
                    ['Isabella King', 'Customer Care', 'Gazipur', '01811223399', '2024-04-16'],
                ],
            ],
        ];
    }
}
