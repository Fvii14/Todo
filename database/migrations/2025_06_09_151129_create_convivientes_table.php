<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConvivientesTable extends Migration
{
    public function up(): void
    {
        Schema::create('convivientes', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('user_id');
            $table->unsignedTinyInteger('index')->comment('Orden del conviviente (1, 2, 3...)');
            $table->timestamps();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'index']); // garantiza que no se repitan convivientes por usuario
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('convivientes');
    }
}
