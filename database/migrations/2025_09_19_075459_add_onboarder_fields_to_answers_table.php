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
        Schema::table('answers', function (Blueprint $table) {
            $table->foreignId('onboarder_id')->nullable()->constrained('onboarders')->onDelete('cascade');
            $table->foreignId('user_conviviente_id')->nullable()->constrained('user_convivientes')->onDelete('cascade');

            $table->index('onboarder_id');
            $table->index('user_conviviente_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('answers', function (Blueprint $table) {
            $table->dropForeign(['onboarder_id']);
            $table->dropForeign(['user_conviviente_id']);
            $table->dropIndex(['onboarder_id']);
            $table->dropIndex(['user_conviviente_id']);
            $table->dropColumn(['onboarder_id', 'user_conviviente_id']);
        });
    }
};
