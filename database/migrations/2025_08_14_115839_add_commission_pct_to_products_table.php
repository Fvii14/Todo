<?php

// database/migrations/2025_08_14_000001_add_commission_pct_to_products_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('commission_pct', 5, 2)->nullable()->after('price'); // ej. 0.00 - 100.00
            $table->index('commission_pct');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['commission_pct']);
            $table->dropColumn('commission_pct');
        });
    }
};
