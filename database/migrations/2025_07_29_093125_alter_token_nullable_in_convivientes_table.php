<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('convivientes', function (Blueprint $table) {
            $table->char('token', 36)->nullable()->default(null)->change();
        });
    }

    public function down(): void
    {
        Schema::table('convivientes', function (Blueprint $table) {
            $table->char('token', 36)->nullable(false)->change();
        });
    }
};
