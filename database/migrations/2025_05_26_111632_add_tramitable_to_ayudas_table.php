<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->boolean('tramitable')->default(true)->after('nombre_ayuda');
        });
    }

    public function down(): void
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->dropColumn('tramitable');
        });
    }
};
