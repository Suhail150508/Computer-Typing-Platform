<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * "শীট টাইপিং": টেবিল/স্প্রেডশিট দেখে ঘরে ঘরে টাইপ করার অনুশীলন।
 * headers = কলামের নাম (ঐচ্ছিক), rows = ২-ডি অ্যারে (সারি × কলাম)।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sheet_exercises', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('icon', 10)->default('📊');
            $table->enum('language', ['bangla', 'english'])->default('bangla');
            $table->unsignedInteger('order')->default(0);
            $table->json('headers')->nullable();
            $table->json('rows');
            $table->unsignedInteger('duration_seconds')->default(180);
            $table->unsignedInteger('min_wpm')->default(10);
            $table->unsignedTinyInteger('min_accuracy')->default(85);
            $table->unsignedTinyInteger('min_duration_percent')->default(60);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['language', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sheet_exercises');
    }
};
