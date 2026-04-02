<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('organos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_organismo');
            $table->string('ambito');
            $table->unsignedBigInteger('id_ccaa')->nullable();
            $table->timestamps(); // Esto creará create_time y update_time
        });
    }

    public function down()
    {
        Schema::dropIfExists('organos');
    }
};
