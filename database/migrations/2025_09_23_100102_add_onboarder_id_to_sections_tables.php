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
        Schema::table('onboarder_sections', function (Blueprint $table) {
            $table->foreignId('onboarder_id')->nullable()->after('id')->constrained('onboarders')->onDelete('cascade');
            $table->index(['onboarder_id', 'order']);
        });
        Schema::table('conviviente_type_sections', function (Blueprint $table) {
            $table->foreignId('onboarder_id')->nullable()->after('id')->constrained('onboarders')->onDelete('cascade');
            $table->index(['onboarder_id', 'order']);
        });
        Schema::table('onboarder_questions', function (Blueprint $table) {
            $table->foreignId('onboarder_id')->nullable()->after('id')->constrained('onboarders')->onDelete('cascade');
            $table->index(['onboarder_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('onboarder_sections', function (Blueprint $table) {
            $table->dropForeign(['onboarder_id']);
            $table->dropIndex(['onboarder_id', 'order']);
            $table->dropColumn('onboarder_id');
        });
        Schema::table('conviviente_type_sections', function (Blueprint $table) {
            $table->dropForeign(['onboarder_id']);
            $table->dropIndex(['onboarder_id', 'order']);
            $table->dropColumn('onboarder_id');
        });
        Schema::table('onboarder_questions', function (Blueprint $table) {
            $table->dropForeign(['onboarder_id']);
            $table->dropIndex(['onboarder_id', 'order']);
            $table->dropColumn('onboarder_id');
        });
    }
};
