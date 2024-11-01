<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->bigIncrements('payment_id');
            $table->foreignId('order_id')->constrained();
            $table->string('payment_number')->unique();
            $table->decimal('total_amount', 10, 2);
            $table->enum('status', [
                'pending',
                'paid',
                'failed',
                'expired',
                'refunded',
                'partially_refunded'
            ]);
            $table->string('payment_method'); // midtrans, manual, etc
            $table->timestamp('paid_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
