<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('passages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lab_id')->constrained('labs')->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->enum('language', ['bangla', 'english'])->default('bangla');
            $table->longText('content');
            $table->unsignedInteger('word_count')->default(0);
            $table->unsignedInteger('char_count')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['lab_id', 'language', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('passages');
    }
};
