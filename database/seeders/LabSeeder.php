<?php

namespace Database\Seeders;

use App\Models\Lab;
use Illuminate\Database\Seeder;

class LabSeeder extends Seeder
{
    public function run(): void
    {
        $labs = [
            ['slug' => 'bcc', 'name_bn' => 'বিসিসি ল্যাব', 'name_en' => 'BCC Lab', 'badge_text' => 'বিসিসি ল্যাব', 'subtitle' => 'শীট ও স্ক্রিন টাইপ | ১১-১৬ তম গ্রেড', 'min_wpm' => 20],
            ['slug' => 'high-court', 'name_bn' => 'হাইকোর্ট ল্যাব', 'name_en' => 'High Court Lab', 'badge_text' => 'হাইকোর্ট ল্যাব', 'subtitle' => 'জুডিশিয়াল, সুপ্রিম কোর্ট | বিজয় ক্ল্যাসিক', 'min_wpm' => 25],
            ['slug' => 'buet', 'name_bn' => 'বুয়েট ল্যাব', 'name_en' => 'BUET Lab', 'badge_text' => 'বুয়েট ল্যাব', 'subtitle' => 'ইউনিকোড টাইপ | ১১-১৬ তম গ্রেড', 'min_wpm' => 20],
            ['slug' => 'software', 'name_bn' => 'সফটওয়্যার টাইপ', 'name_en' => 'Software Typing', 'badge_text' => 'সফটওয়্যার টাইপ', 'subtitle' => 'ল্যাব ভিত্তিক | ১১-১৬ তম গ্রেড', 'min_wpm' => 20],
            ['slug' => 'practical', 'name_bn' => 'ব্যবহারিক পরীক্ষা', 'name_en' => 'Practical Exam', 'badge_text' => 'ব্যবহারিক পরীক্ষা', 'subtitle' => 'গ্রেড ১১-১৩ তম', 'min_wpm' => 20],
        ];

        foreach ($labs as $i => $lab) {
            Lab::updateOrCreate(['slug' => $lab['slug']], $lab + ['sort_order' => $i + 1, 'is_active' => true]);
        }
    }
}
