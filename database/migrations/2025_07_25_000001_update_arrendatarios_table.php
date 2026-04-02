<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('arrendatarios', function (Blueprint $table) {
            $table->unsignedTinyInteger('index')->after('user_id')->default(1)->comment('Orden del arrendador (1, 2, 3...)');
            $table->unique(['user_id', 'index']);
        });
    }

    public function down(): void
    {
        Schema::table('arrendatarios', function (Blueprint $table) {
            $table->dropUnique(['user_id', 'index']);
            $table->dropColumn('index');
        });
    }
};
