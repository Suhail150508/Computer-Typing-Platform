<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * আলাদা (additive) migration — পুরনো ডাটা অক্ষত থাকে, শুধু `php artisan migrate` চালালেই হবে।
 *  - labs.durations            : ল্যাবে ইউজার কোন কোন সময় (মিনিট, কমা দিয়ে) বেছে নিতে পারবে
 *  - typing_attempts.speed_samples      : প্রতি সেকেন্ডে মোট টাইপ করা অক্ষর (স্পিড চার্টের জন্য)
 *  - typing_attempts.sheet_exercise_id  : শীট টাইপিংয়ের অ্যাটেম্পট চেনার জন্য
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('labs', function (Blueprint $table) {
            $table->string('durations', 100)->nullable()->after('min_duration_percent');
        });

        Schema::table('typing_attempts', function (Blueprint $table) {
            $table->json('speed_samples')->nullable()->after('diff_meta');
            $table->foreignId('sheet_exercise_id')->nullable()->after('practice_card_id')
                ->constrained('sheet_exercises')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('typing_attempts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('sheet_exercise_id');
            $table->dropColumn('speed_samples');
        });

        Schema::table('labs', function (Blueprint $table) {
            $table->dropColumn('durations');
        });
    }
};
