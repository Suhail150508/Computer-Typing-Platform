<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs')) {
            return;
        }

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->nullable()->index();
            $table->string('visitor', 16)->nullable()->index();   // অতিথির আইডি (সেশন থেকে হ্যাশ)
            $table->string('type', 30)->index();
            $table->string('label', 255);
            $table->string('path', 200)->nullable();
            $table->text('meta')->nullable();
            $table->string('ip', 45)->nullable();
            $table->string('device', 12)->nullable();             // mobile / desktop / tablet
            $table->timestamp('created_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');
    }
};
