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
        Schema::create('shipments', function (Blueprint $table) {
            $table->bigIncrements('shipment_id');
            $table->foreignId('order_id')->constrained();
            $table->string('shipment_number')->unique();
            $table->enum('status', [
                'pending',
                'processing',
                'shipped',
                'delivered',
                'failed',
                'returned'
            ]);
            $table->foreignId('shipping_address_id')->constrained();
            $table->decimal('total_weight', 10, 2)->comment('dalam gram');
            $table->decimal('total_cost', 10, 2);
            $table->timestamp('shipped_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
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
        Schema::dropIfExists('shipments');
    }
};
