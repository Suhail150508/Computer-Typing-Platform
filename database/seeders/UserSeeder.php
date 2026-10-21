<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(['email' => 'admin@typingbd.test'], [
            'name' => 'অ্যাডমিন',
            'password' => 'password',
            'role' => 'admin',
            'subscription_expires_at' => now()->addYear(),
        ]);

        User::updateOrCreate(['email' => 'demo@typingbd.test'], [
            'name' => 'ডেমো ইউজার',
            'phone' => '01700000000',
            'password' => 'password',
            'role' => 'user',
            'subscription_expires_at' => now()->addDays(30),
        ]);
    }
}
