<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('crm_state_history', function (Blueprint $table) {
            $table->unsignedBigInteger('ayuda_id')->nullable()->after('id');

            $table->foreign('ayuda_id')
                ->references('id')
                ->on('ayudas')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('crm_state_history', function (Blueprint $table) {
            $table->dropForeign(['ayuda_id']);
            $table->dropColumn('ayuda_id');
        });
    }
};
