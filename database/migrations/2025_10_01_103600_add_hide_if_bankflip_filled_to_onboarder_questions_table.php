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
        Schema::table('onboarder_questions', function (Blueprint $table) {
            $table->boolean('hide_if_bankflip_filled')->default(false)->after('block_if_bankflip_filled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarder_questions', function (Blueprint $table) {
            $table->dropColumn('hide_if_bankflip_filled');
        });
    }
};
