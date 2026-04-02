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
        Schema::create('ayuda_enlaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ayuda_id')->constrained()->onDelete('cascade');
            $table->string('texto_boton');
            $table->longText('url');
            $table->integer('orden')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ayuda_enlaces');
    }
};
