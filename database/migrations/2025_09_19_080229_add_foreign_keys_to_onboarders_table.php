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
        Schema::table('onboarders', function (Blueprint $table) {
            $table->foreign('current_section_id')->references('id')->on('onboarder_sections')->onDelete('set null');
            $table->foreign('current_conviviente_type_id')->references('id')->on('conviviente_types')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarders', function (Blueprint $table) {
            $table->dropForeign(['current_section_id']);
            $table->dropForeign(['current_conviviente_type_id']);
        });
    }
};
