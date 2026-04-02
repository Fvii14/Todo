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
        Schema::create('ayuda_pre_requisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayuda_id')->constrained('ayudas')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('type', ['simple', 'group', 'complex'])->default('simple');
            $table->enum('target_type', ['solicitante', 'conviviente', 'unidad_convivencia_completa', 'unidad_convivencia_sin_solicitante', 'unidad_familiar_completa', 'unidad_familiar_sin_solicitante', 'any_conviviente', 'any_familiar', 'any_persona_unidad'])->default('solicitante');
            $table->string('conviviente_type')->nullable();
            $table->string('target_conviviente_type')->nullable();
            $table->foreignId('question_id')->nullable()->constrained('questions')->onDelete('cascade');
            $table->string('operator')->nullable();
            $table->json('value')->nullable();
            $table->json('value2')->nullable();
            $table->enum('value_type', ['exact', 'relative_date', 'age_minimum', 'age_maximum', 'age_range'])->nullable();
            $table->enum('age_unit', ['years', 'months', 'days'])->nullable();
            $table->enum('group_logic', ['AND', 'OR'])->nullable();
            $table->boolean('is_required')->default(true);
            $table->text('error_message')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->index(['ayuda_id', 'active']);
            $table->index(['target_type', 'active']);
            $table->index(['type', 'active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayuda_pre_requisitos');
    }
};
