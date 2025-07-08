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
        Schema::create('shop_addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_info_id')->constrained('shop_infos')->onDelete('cascade');
            $table->string('address_line_1')->comment('First line of the address');
            $table->string('address_line_2')->nullable()->comment('Second line of the address, optional');
            $table->string('city')->nullable()->comment('City of the shop');
            $table->string('state')->nullable()->comment('State or region of the shop');
            $table->string('postal_code')->nullable()->comment('Postal or ZIP code of the shop');
            $table->string('latitude')->nullable();
            $table->string('longitude')->nullable();
            $table->boolean('display_my_address')->default(false)->comment('Whether to display the shop address publicly');
            $table->boolean('address_10_mile')->default(false)->comment('Whether the address is within a 10-mile radius for delivery or service purposes');
            $table->boolean('do_not_display')->default(false)->comment('Whether to hide the address from public view');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_addresses');
    }
};
