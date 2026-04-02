<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fases_ayudas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('ayuda_id')
                ->constrained('ayudas')
                ->cascadeOnUpdate()
                ->cascadeOnDelete();

            $table->string('fase');
            $table->timestamps();

            $table->index('fase');
            $table->unique(['ayuda_id', 'fase']);

            $table->foreign('fase')
                ->references('slug')
                ->on('fase')
                ->cascadeOnUpdate()
                ->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fases_ayudas');
    }
};
