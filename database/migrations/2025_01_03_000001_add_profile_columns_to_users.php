<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * প্রোফাইল ও ইউজার-ম্যানেজমেন্টের জন্য users টেবিলে নতুন কলাম।
 * আলাদা (additive) migration — পুরনো ডাটা অক্ষত থাকে; `php artisan migrate` চালালেই হবে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('type_code', 8)->nullable()->unique()->after('id');
            $table->string('institute', 120)->nullable()->after('phone');
            $table->string('district', 60)->nullable()->after('institute');
            $table->timestamp('password_set_at')->nullable()->after('password');
            $table->timestamp('last_login_at')->nullable()->after('is_active');
            $table->string('deactivated_reason')->nullable()->after('last_login_at');
            $table->timestamp('deactivated_at')->nullable()->after('deactivated_reason');
        });

        // বিদ্যমান ইউজারদের জন্য টাইপ কোড তৈরি এবং (গুগল ছাড়া) পাসওয়ার্ড-সেট চিহ্নিত করা
        $used = [];
        DB::table('users')->orderBy('id')->get(['id', 'google_id', 'created_at'])->each(function ($u) use (&$used) {
            do {
                $code = (string) random_int(100000, 999999);
            } while (isset($used[$code]));
            $used[$code] = true;

            DB::table('users')->where('id', $u->id)->update([
                'type_code' => $code,
                'password_set_at' => $u->google_id ? null : ($u->created_at ?? now()),
            ]);
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['type_code', 'institute', 'district', 'password_set_at', 'last_login_at', 'deactivated_reason', 'deactivated_at']);
        });
    }
};
