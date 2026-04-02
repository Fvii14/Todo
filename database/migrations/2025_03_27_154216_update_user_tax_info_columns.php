<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->string('base_imponible_general', 20)->change();
            $table->string('base_imponible_ahorro', 20)->change();
        });
    }

    public function down(): void
    {
        Schema::table('user_tax_info', function (Blueprint $table) {
            $table->bigInteger('base_imponible_general')->change();
            $table->bigInteger('base_imponible_ahorro')->change();
        });
    }
};
