<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->unsignedBigInteger('arrendador_id')->nullable()->after('conviviente_id');
            $table->foreign('arrendador_id')->references('id')->on('arrendatarios')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign(['arrendador_id']);
            $table->dropColumn('arrendador_id');
        });
    }
};
