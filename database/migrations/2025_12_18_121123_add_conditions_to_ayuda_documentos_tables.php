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
        Schema::table('ayuda_documentos', function (Blueprint $table) {
            $table->json('conditions')->nullable()->after('es_obligatorio');
        });

        Schema::table('ayuda_documentos_convivientes', function (Blueprint $table) {
            $table->json('conditions')->nullable()->after('es_obligatorio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ayuda_documentos', function (Blueprint $table) {
            $table->dropColumn('conditions');
        });

        Schema::table('ayuda_documentos_convivientes', function (Blueprint $table) {
            $table->dropColumn('conditions');
        });
    }
};
