<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('feedback_reports')) {
            return;
        }

        Schema::create('feedback_reports', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 20)->default('problem'); // problem | idea | other
            $table->text('message');
            $table->string('screenshot')->nullable();
            $table->string('page_url', 500)->nullable();
            $table->string('user_agent', 300)->nullable();
            $table->string('status', 20)->default('new'); // new | seen | resolved
            $table->timestamps();
            $table->index('status');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback_reports');
    }
};
