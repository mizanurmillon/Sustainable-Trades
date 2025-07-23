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
        Schema::create('trade_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('trade_offer_id')->constrained('trade_offers')->cascadeOnDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->cascadeOnDelete('cascade');
            $table->integer('quantity')->default(1);
            $table->enum('type', ['offered','requested'])->default('offered');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trade_items');
    }
};
