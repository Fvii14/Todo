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
        Schema::create('onboarders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('wizard_id')->constrained('wizards')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->enum('status', ['draft', 'in_progress', 'completed', 'abandoned'])->default('draft');
            $table->json('data')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->unsignedBigInteger('current_section_id')->nullable();
            $table->unsignedBigInteger('current_conviviente_type_id')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'wizard_id']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarders');
    }
};
