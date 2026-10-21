<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Google লগইনের জন্য users টেবিলে নতুন কলাম — আলাদা (additive) migration,
 * যাতে আগে থেকে migrate করা প্রজেক্টেও `php artisan migrate` চালিয়ে
 * নিরাপদে যোগ করা যায়, কোনো ডাটা না হারিয়ে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('google_id')->nullable()->unique()->after('avatar');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('google_id');
        });
    }
};
