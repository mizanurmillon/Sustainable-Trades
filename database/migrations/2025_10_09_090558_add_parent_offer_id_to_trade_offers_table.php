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
        Schema::table('trade_offers', function (Blueprint $table) {
            $table->unsignedBigInteger('parent_offer_id')->nullable()->after('status');
            $table->foreign('parent_offer_id')->references('id')->on('trade_offers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('trade_offers', function (Blueprint $table) {
            //
        });
    }
};
