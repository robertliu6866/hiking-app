<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trip_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trip_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->string('merchant_order_id')->unique();
            $table->unsignedInteger('amount');
            $table->string('currency', 3)->default('TWD');
            $table->string('payment_method')->nullable();
            $table->string('status')->default('pending');
            $table->string('provider_transaction_id')->nullable();
            $table->string('bank_transfer_name')->nullable();
            $table->string('bank_transfer_last_five', 5)->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('raw_response')->nullable();
            $table->timestamps();

            $table->index(['trip_id', 'user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trip_orders');
    }
};
