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
        Schema::table('wizards', function (Blueprint $table) {
            $table->unsignedBigInteger('duplicated_from_id')->nullable()->after('description');
            $table->string('duplication_reason')->nullable()->after('duplicated_from_id');
            $table->timestamp('duplicated_at')->nullable()->after('duplication_reason');

            $table->foreign('duplicated_from_id')->references('id')->on('wizards')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wizards', function (Blueprint $table) {
            $table->dropForeign(['duplicated_from_id']);
            $table->dropColumn(['duplicated_from_id', 'duplication_reason', 'duplicated_at']);
        });
    }
};
