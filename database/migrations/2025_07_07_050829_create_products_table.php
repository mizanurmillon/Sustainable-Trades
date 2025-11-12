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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_info_id')
                ->constrained('shop_infos')
                ->onDelete('cascade');
            $table->string('product_name');
            $table->float('product_price');
            $table->integer('product_quantity')->nullable();
            $table->boolean('unlimited_stock')->default(false);
            $table->boolean('out_of_stock')->default(false);
            $table->string('video')->nullable();
            $table->text('description')->nullable();
            $table->foreignId('category_id')
                ->constrained('categories')
                ->onDelete('cascade');
            $table->foreignId('sub_category_id')->nullable()
                ->constrained('sub_categories')
                ->onDelete('cascade');
            $table->string('fulfillment')->nullable();
            $table->string('selling_option')->nullable();
            $table->enum('status', ['listing','pending','approved','rejected','cancelled'])->default('listing');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
