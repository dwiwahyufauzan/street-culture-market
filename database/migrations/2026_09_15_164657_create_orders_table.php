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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number', 50)->unique();
            $table->string('status', 30)->default('pending')->index(); // pending, processing, shipped, delivered, cancelled
            $table->string('payment_status', 30)->default('unpaid')->index(); // unpaid, paid, refunded
            $table->string('payment_method', 50)->nullable();
            $table->string('midtrans_order_id', 100)->nullable()->index();
            $table->string('midtrans_snap_token', 255)->nullable();

            // Customer snapshot
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone', 20)->nullable();

            // Shipping
            $table->text('shipping_address')->nullable();
            $table->string('shipping_city', 100)->nullable();
            $table->string('shipping_province', 100)->nullable();
            $table->string('shipping_postal', 10)->nullable();
            $table->string('shipping_method', 100)->nullable();
            $table->decimal('shipping_cost', 12, 2)->default(0);

            // Financials
            $table->decimal('subtotal', 12, 2);
            $table->decimal('discount_amount', 12, 2)->default(0);
            $table->decimal('total', 12, 2);

            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
