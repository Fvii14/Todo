<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('onboarder_questions', function (Blueprint $table) {
            $table->unsignedInteger('screen')->default(0)->after('order');
        });
    }

    public function down(): void
    {
        Schema::table('onboarder_questions', function (Blueprint $table) {
            $table->dropColumn('screen');
        });
    }
};
