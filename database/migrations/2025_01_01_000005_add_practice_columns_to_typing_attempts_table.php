<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "টাইপিং শুরু" ফিচারের জন্য typing_attempts টেবিলে নতুন কলাম।
 * এটি ইচ্ছাকৃতভাবে একটি আলাদা (additive) migration — মূল টেবিল তৈরির
 * migration ফাইলটি স্পর্শ করা হয়নি, যাতে আগে থেকে migrate করা প্রজেক্টেও
 * শুধু `php artisan migrate` চালিয়ে নিরাপদে নতুন কলাম যোগ করা যায়,
 * কোনো ডাটা না হারিয়ে।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('typing_attempts', function (Blueprint $table) {
            $table->foreignId('practice_card_id')->nullable()->after('lab_id')
                ->constrained('practice_cards')->nullOnDelete();
            $table->enum('source', ['lab', 'practice'])->default('lab')->after('practice_card_id');

            $table->index(['user_id', 'practice_card_id', 'result']);
        });
    }

    public function down(): void
    {
        Schema::table('typing_attempts', function (Blueprint $table) {
            $table->dropIndex(['user_id', 'practice_card_id', 'result']);
            $table->dropConstrainedForeignId('practice_card_id');
            $table->dropColumn('source');
        });
    }
};
