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
        Schema::create('discounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shop_infos')->onDelete('cascade');
            $table->string('name')->comment('Name of the discount');
            $table->enum('discount_type',['automatic_discount','discount_code'])->default('discount_code')
                ->comment('Type of discount, e.g., automatic discount or coupon code');
            $table->string('code')->nullable()->comment('Discount code for coupon-based discounts');
            $table->enum('promotion_type', ['percentage', 'fixed'])
                ->default('percentage')
                ->comment('Method of discount, either percentage or fixed amount');
            $table->decimal('amount', 8, 2)->nullable()->comment('Amount of the discount');
            $table->enum('applies', ['any_order','single_product'])->nullable()->comment(
                'Indicates if the discount applies to any order or a single product');
            $table->foreignId('product_id')->nullable()->constrained('products')->onDelete('cascade');
            $table->integer('discount_limits')
                ->default(0)
                ->comment('Number of times the discount can be used, 0 means unlimited');
            $table->date('start_date')->nullable()->comment('Start date for the discount');
            $table->date('end_date')->nullable()->comment('End date for the discount');
            $table->time('start_time')->nullable()->comment('Start time for the discount');
            $table->time('end_time')->nullable()->comment('End time for the discount');
            $table->boolean('never_expires')
                ->default(false)
                ->comment('Indicates if the discount never expires');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discounts');
    }
};
