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
            $table->renameColumn('id_user', 'user_id');
            $table->renameColumn('id_ayuda', 'ayuda_id');

            $table->dropColumn(['benef']);

            $table->string('tags')->nullable()->after('ayuda_id');
            $table->dateTime('ultimo_contacto')->nullable()->after('tags');
            $table->dateTime('fecha_formulario')->nullable()->after('ultimo_contacto');
            $table->string('estado_comercial')->nullable()->after('fecha_formulario');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_ayudas', function (Blueprint $table) {
            $table->renameColumn('user_id', 'id_user');
            $table->renameColumn('ayuda_id', 'id_ayuda');
            $table->tinyInteger('benef')->default(0)->after('id_ayuda');
            $table->dropColumn(['tags', 'ultimo_contacto', 'fecha_formulario', 'estado_comercial']);
        });
    }
};
