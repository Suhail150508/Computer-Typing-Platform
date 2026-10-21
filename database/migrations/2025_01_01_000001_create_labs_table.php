<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('labs', function (Blueprint $table) {
            $table->id();
            $table->string('name_bn');
            $table->string('name_en')->nullable();
            $table->string('slug')->unique();
            $table->string('badge_text')->nullable();
            $table->string('subtitle')->nullable();
            $table->unsignedInteger('min_wpm')->default(20);
            $table->unsignedTinyInteger('min_accuracy')->default(90);
            $table->unsignedTinyInteger('min_duration_percent')->default(90);
            $table->unsignedInteger('sort_order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('labs');
    }
};
