<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->string('id_producto', 255)->nullable()->after('stripe_product_id');
        });

        // Actualizar valores específicos
        DB::table('products')->where('id', 1)->update([
            'id_producto' => 'price_1RgMxpCzWJhmKBW31pw9T8QV',
        ]);
        DB::table('products')->where('id', 2)->update([
            'id_producto' => 'price_1RgMsDCzWJhmKBW3eJSsrDYj',
        ]);
        DB::table('products')->where('id', 3)->update([
            'id_producto' => 'price_1RgMwACzWJhmKBW3SrCzRs3i',
        ]);
        DB::table('products')->where('id', 4)->update([
            'id_producto' => 'price_1RgMvGCzWJhmKBW3I63fF8OD',
        ]);
        DB::table('products')->where('id', 6)->update([
            'id_producto' => 'price_1Q4iyCCzWJhmKBW3e1bb3KXf',
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn('id_producto');
        });
    }
};
