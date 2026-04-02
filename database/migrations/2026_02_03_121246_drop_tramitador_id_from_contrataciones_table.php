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
        if (Schema::hasColumn('contrataciones', 'tramitador_id')) {
            Schema::table('contrataciones', function (Blueprint $table) {
                $table->dropForeign(['tramitador_id']);
                $table->dropColumn('tramitador_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->unsignedBigInteger('tramitador_id')->nullable()->after('user_id');
            $table->foreign('tramitador_id')
                ->references('id')
                ->on('users')
                ->onDelete('set null');
        });
    }
};
