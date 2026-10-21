<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('practice_cards', function (Blueprint $table) {
            $table->id();
            $table->enum('tier', ['basic', 'advance', 'hard'])->default('basic');
            $table->unsignedInteger('order')->default(0);
            $table->string('title');
            $table->string('description')->nullable();
            $table->string('icon', 10)->default('⌨️');
            $table->enum('language', ['bangla', 'english'])->default('bangla');
            $table->longText('content');
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedInteger('char_count')->default(0);
            $table->unsignedInteger('duration_seconds')->default(60);
            $table->unsignedInteger('min_wpm')->default(10);
            $table->unsignedTinyInteger('min_accuracy')->default(85);
            $table->unsignedTinyInteger('min_duration_percent')->default(80);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['tier', 'order']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('practice_cards');
    }
};
