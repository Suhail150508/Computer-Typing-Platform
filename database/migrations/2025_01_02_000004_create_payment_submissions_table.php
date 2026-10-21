<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * ইউজার ম্যানুয়ালি bKash/Nagad/Rocket এ টাকা পাঠানোর পর এখানে
 * ট্রানজেকশন তথ্য জমা দেয়; অ্যাডমিন যাচাই করে Approve/Reject করেন।
 * Approve করলে User::extendSubscription() কল হয়ে একাউন্ট সক্রিয় হয়ে যায়।
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('subscription_plan_id')->constrained('subscription_plans')->cascadeOnDelete();
            $table->enum('platform', ['bkash', 'nagad', 'rocket'])->default('bkash');
            $table->string('sender_number', 20);
            $table->string('transaction_id', 50);
            $table->decimal('amount', 10, 2);
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('admin_note')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_submissions');
    }
};
