<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->boolean('liquidada')->default(false)->after('monto_total_ayuda');
            $table->index(['ayuda_id', 'estado', 'liquidada']);
        });
    }

    public function down(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->dropIndex(['contrataciones_ayuda_id_estado_liquidada_index']);
            $table->dropColumn('liquidada');
        });
    }
};
