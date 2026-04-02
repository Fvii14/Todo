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
        Schema::create('conviviente_type_sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conviviente_type_id')->constrained('conviviente_types')->onDelete('cascade');
            $table->string('name');
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->json('skip_condition')->nullable();
            $table->boolean('is_required')->default(true);
            $table->boolean('is_skippeable')->default(false);
            $table->timestamps();

            $table->index(['conviviente_type_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conviviente_type_sections');
    }
};
