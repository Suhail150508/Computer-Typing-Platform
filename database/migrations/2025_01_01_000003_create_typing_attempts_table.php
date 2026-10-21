<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('typing_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('passage_id')->nullable()->constrained('passages')->nullOnDelete();
            $table->foreignId('lab_id')->nullable()->constrained('labs')->nullOnDelete();
            $table->enum('language', ['bangla', 'english'])->default('bangla');
            $table->unsignedInteger('duration_seconds')->default(0);
            $table->unsignedInteger('time_taken_seconds')->default(0);
            $table->longText('typed_text')->nullable();
            $table->longText('original_text')->nullable();
            $table->unsignedInteger('total_chars')->default(0);
            $table->unsignedInteger('total_chars_no_space')->default(0);
            $table->unsignedInteger('correct_chars_no_space')->default(0);
            $table->unsignedInteger('wrong_chars_no_space')->default(0);
            $table->unsignedInteger('total_words')->default(0);
            $table->unsignedInteger('wrong_words')->default(0);
            $table->decimal('wpm', 6, 2)->default(0);
            $table->decimal('accuracy', 5, 2)->default(0);
            $table->decimal('error_percent', 5, 2)->default(0);
            $table->enum('result', ['pass', 'fail'])->default('fail');
            $table->json('diff_meta')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'created_at']);
            $table->index(['user_id', 'result']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('typing_attempts');
    }
};
