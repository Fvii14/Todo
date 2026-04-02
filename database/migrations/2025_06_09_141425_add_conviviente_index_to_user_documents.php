<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $table->unsignedTinyInteger('conviviente_index')
                ->nullable()
                ->after('document_id')
                ->comment('Número del conviviente (1, 2, 3...) si el documento corresponde a uno de ellos');
        });
    }

    public function down(): void
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $table->dropColumn('conviviente_index');
        });
    }
};
