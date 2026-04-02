<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('convivientes', function (Blueprint $table) {
            $table->uuid('token')->unique()->nullable()->after('index');
        });
    }

    public function down()
    {
        Schema::table('convivientes', function (Blueprint $table) {
            $table->dropColumn('token');
        });
    }
};
