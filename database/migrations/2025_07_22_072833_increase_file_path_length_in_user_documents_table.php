<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $table->string('file_path', 1024)->change();
        });
    }

    public function down()
    {
        Schema::table('user_documents', function (Blueprint $table) {
            $table->string('file_path', 255)->change();
        });
    }
};
