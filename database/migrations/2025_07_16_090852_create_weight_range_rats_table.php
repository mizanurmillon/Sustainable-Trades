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
        Schema::create('weight_range_rats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shop_id')->constrained('shop_infos')->onDelete('cascade');
            $table->string('min_weight')->comment('Minimum weight for the range');
            $table->string('max_weight')->comment('Maximum weight for the range');
            $table->decimal('cost', 8, 2)->comment('Cost for the weight range in the specified currency');
            $table->timestamps();

        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('weight_range_rats');
    }
};
