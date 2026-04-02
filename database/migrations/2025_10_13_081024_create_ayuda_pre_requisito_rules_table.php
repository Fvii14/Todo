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
        Schema::create('ayuda_pre_requisito_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pre_requisito_id')->constrained('ayuda_pre_requisitos')->onDelete('cascade');
            $table->foreignId('question_id')->constrained('questions')->onDelete('cascade');
            $table->string('operator');
            $table->json('value')->nullable();
            $table->json('value2')->nullable();
            $table->integer('order')->default(0);
            $table->timestamps();

            $table->index(['pre_requisito_id', 'order']);
            $table->index(['question_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayuda_pre_requisito_rules');
    }
};
