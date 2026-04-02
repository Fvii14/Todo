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
        Schema::table('user_ayudas', function (Blueprint $table) {
            $table->unsignedBigInteger('ayuda_id')->nullable()->change();
            $table->string('tags')->nullable()->change();
            $table->dateTime('fecha_formulario')->nullable()->change();
            $table->dropColumn('ultimo_contacto');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_ayudas', function (Blueprint $table) {
            $table->unsignedBigInteger('ayuda_id')->nullable(false)->change();
            $table->string('tags')->nullable(false)->change();
            $table->dateTime('fecha_formulario')->nullable(false)->change();
            $table->dateTime('ultimo_contacto')->nullable()->after('tags');
        });
    }
};
