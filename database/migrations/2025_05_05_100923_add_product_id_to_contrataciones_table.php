<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductIdToContratacionesTable extends Migration
{
    public function up()
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->unsignedBigInteger('product_id')->nullable()->after('user_id');

            $table->foreign('product_id')
                ->references('id')
                ->on('products')
                ->onDelete('set null'); // o 'cascade' si quieres eliminar la contratación al borrar el producto
        });
    }

    public function down()
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn('product_id');
        });
    }
}
