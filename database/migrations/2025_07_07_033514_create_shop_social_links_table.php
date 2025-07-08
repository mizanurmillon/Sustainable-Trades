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
        Schema::create('shop_social_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_info_id')->constrained('shop_infos')->onDelete('cascade');
            $table->string('platform')->comment('e.g., Facebook, Twitter, Instagram');
            $table->string('url')->comment('The URL of the social media profile');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_social_links');
    }
};
