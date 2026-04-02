<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('questions')
            ->where('id', 32)
            ->update(['exclude_none_option' => true]);
    }

    public function down(): void
    {
        DB::table('questions')
            ->where('id', 32)
            ->update(['exclude_none_option' => false]);
    }
};
