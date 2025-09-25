<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void {
        Schema::create('dynamic_pages', function (Blueprint $table) {
            $table->id();
            $table->string('page_title')->nullable();
            $table->string('page_slug')->nullable();
            $table->string('sub_title')->nullable();
            $table->longText('page_content')->nullable();
            $table->string('icon')->nullable();
            $table->string('page_image')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void {
        Schema::dropIfExists('dynamic_pages');
    }
};
