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
        Schema::create('membership_histories', function (Blueprint $table) {
            $table->id();
            $table->integer('order_id');
            $table->foreignId('membership_id')->constrained('memberships')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('plan_id')->constrained('subscription_plans')->onDelete('cascade');
            $table->enum('membership_type', ['pro', 'basic'])->default('basic');
            $table->enum('type', ['monthly', 'yearly'])->default('monthly');
            $table->decimal('price', 10, 2)->default(0.00);
            $table->dateTime('start_at')->nullable();
            $table->dateTime('end_at')->nullable();
            $table->dateTime('cancel_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('membership_histories');
    }
};
