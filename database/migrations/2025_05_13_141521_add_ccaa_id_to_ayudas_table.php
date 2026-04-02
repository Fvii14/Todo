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
        Schema::table('ayudas', function (Blueprint $table) {
            $table->unsignedBigInteger('ccaa_id')->nullable()->after('id');

            $table->foreign('ccaa_id')
                ->references('id')
                ->on('ccaa')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ayudas', function (Blueprint $table) {
            $table->dropForeign(['ccaa_id']);
            $table->dropColumn('ccaa_id');
        });
    }
};
