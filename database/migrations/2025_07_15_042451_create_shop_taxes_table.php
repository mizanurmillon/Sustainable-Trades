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
        Schema::create('shop_taxes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shop_infos')->onDelete('cascade');
            $table->string('country')->nullable()->comment('Country code for the tax');
            $table->string('state')->nullable()->comment('State code for the tax, if applicable');
            $table->decimal('rate', 5, 2)->comment('Tax rate as a percentage');
            $table->boolean('is_digital_products')->default(true)->comment('Indicates if the tax applies to digital products');
            $table->boolean('is_shipping')->default(true)->comment('Indicates if the tax applies to shipping costs');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_taxes');
    }
};
