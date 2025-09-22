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
        Schema::create('about_shops', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_info_id')->constrained('shop_infos')->onDelete('cascade');
            $table->string('tagline')->nullable();
            $table->text('statement')->nullable();
            $table->text('our_story')->nullable();
            $table->string('about_image')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('about_shops');
    }
};
