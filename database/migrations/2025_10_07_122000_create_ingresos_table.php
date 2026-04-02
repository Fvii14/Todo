<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ingresos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unsignedBigInteger('conviviente_id')->nullable();
            $table->foreign('conviviente_id')->references('id')->on('convivientes')->nullOnDelete();
            $table->string('tipo');
            $table->unsignedTinyInteger('meses');
            $table->decimal('importe_medio', 12, 2);
            $table->decimal('importe_anual', 12, 2);
            $table->timestamps();

            $table->index('user_id');
            $table->index('conviviente_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ingresos');
    }
};
