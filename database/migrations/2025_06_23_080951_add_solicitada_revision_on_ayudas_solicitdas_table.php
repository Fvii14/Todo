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
        Schema::table('ayudas_solicitadas', function (Blueprint $table) {
            $table->boolean('solicitada_revision')->nullable()->after('observaciones');
            $table->text('info_revision')->nullable()->after('solicitada_revision');
            $table->text('motivo_rechazo')->nullable()->after('info_revision');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ayudas_solicitadas', function (Blueprint $table) {
            $table->dropColumn('solicitada_revision');
            $table->dropColumn('info_revision');
            $table->dropColumn('motivo_rechazo');
        });
    }
};
