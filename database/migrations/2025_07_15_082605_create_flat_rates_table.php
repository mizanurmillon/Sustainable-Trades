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
        Schema::create('flat_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shop_infos')->onDelete('cascade');
            $table->string('option_name')->nullable();
            $table->decimal('per_order_fee', 10, 2)->default(0.00);
            $table->decimal('per_item_fee',10,2)->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('flat_rates');
    }
};
