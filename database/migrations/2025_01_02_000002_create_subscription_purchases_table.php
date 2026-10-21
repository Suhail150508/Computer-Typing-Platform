<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->nullable()->constrained('subscription_plans')->nullOnDelete();
            $table->decimal('amount_paid', 10, 2)->default(0);
            $table->string('payment_method')->default('manual'); // manual | bkash | nagad | rocket | ...
            $table->string('reference')->nullable();
            $table->timestamp('starts_at');
            $table->timestamp('ends_at');
            $table->enum('status', ['active', 'expired', 'cancelled'])->default('active');
            $table->text('note')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_purchases');
    }
};
