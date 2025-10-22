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
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('shop_id')->constrained('shop_infos')->onDelete('cascade');
            $table->string('order_number', 50)->unique();
            $table->decimal('sub_total', 10, 2)->default(0);
            $table->decimal('total_amount', 10, 2)->default(0);
            $table->decimal('tax_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->string('payment_method', 50)->default('cash_on_delivery');
            $table->string('payment_status', 50)->default('pending');
            $table->string('currency', 10)->default('USD');
            $table->enum('shipping_option', ['pickup', 'delivery'])->default('delivery');
            $table->enum('status', ['pending','confirmed','processing','shipped','delivered','cancelled'])->default('pending');
            $table->timestamp('delivered_at')->nullable();
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
