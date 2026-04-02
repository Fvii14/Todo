<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->text('text_conviviente')->nullable()->after('sub_text');
            $table->text('sub_text_conviviente')->nullable()->after('text_conviviente');
        });
    }

    public function down(): void
    {
        Schema::table('questions', function (Blueprint $table) {
            $table->dropColumn(['text_conviviente', 'sub_text_conviviente']);
        });
    }
};
