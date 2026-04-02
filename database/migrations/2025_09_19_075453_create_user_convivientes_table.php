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
        Schema::create('user_convivientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarder_id')->constrained('onboarders')->onDelete('cascade');
            $table->foreignId('conviviente_type_id')->constrained('conviviente_types')->onDelete('cascade');
            $table->json('data')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['onboarder_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_convivientes');
    }
};
