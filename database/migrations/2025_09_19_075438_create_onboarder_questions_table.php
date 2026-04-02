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
        Schema::create('onboarder_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('onboarder_section_id')->constrained('onboarder_sections')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->integer('order')->default(0);
            $table->json('condition')->nullable();
            $table->json('required_condition')->nullable();
            $table->json('optional_condition')->nullable();
            $table->boolean('block_if_bankflip_filled')->default(false);
            $table->boolean('is_builder')->default(false);
            $table->timestamps();

            $table->index(['onboarder_section_id', 'order'], 'oq_section_order_idx');
            $table->unique(['onboarder_section_id', 'question_id'], 'oq_section_question_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('onboarder_questions');
    }
};
