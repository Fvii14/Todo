<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddProductIdToPaymentsTable extends Migration
{
    public function up()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (! Schema::hasColumn('payments', 'producto_id')) {
                $table->unsignedBigInteger('producto_id')->nullable()->after('email');

                $table->foreign('producto_id')
                    ->references('id')
                    ->on('products')
                    ->onDelete('set null'); // o 'cascade' si prefieres eliminar los pagos al borrar el producto
            }
        });
    }

    public function down()
    {
        Schema::table('payments', function (Blueprint $table) {
            if (Schema::hasColumn('payments', 'producto_id')) {
                $table->dropForeign(['producto_id']);
                $table->dropColumn('producto_id');
            }
        });
    }
}
