<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateProductColumnInPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Verificamos si existe la columna vieja
            if (Schema::hasColumn('payments', 'producto_id')) {
                $table->dropForeign('payments_producto_id_foreign');
                $table->dropColumn('producto_id');
            }

            // Agregamos la nueva columna solo si no existe
            if (! Schema::hasColumn('payments', 'product_id')) {
                $table->unsignedBigInteger('product_id')->nullable(); // puedes quitar nullable si es obligatorio

                $table->foreign('product_id')
                    ->references('id')->on('products')
                    ->onDelete('set null'); // puedes cambiar a cascade, restrict, etc.
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            // Quitamos la nueva columna
            if (Schema::hasColumn('payments', 'product_id')) {
                $table->dropForeign(['product_id']);
                $table->dropColumn('product_id');
            }

            // Restauramos la columna anterior
            if (! Schema::hasColumn('payments', 'producto_id')) {
                $table->integer('producto_id')->nullable(); // cambia tipo si era diferente
            }
        });
    }
}
