<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateProductsTable extends Migration
{
    public function up()
    {
        Schema::create('products', function (Blueprint $table) {
            $table->bigIncrements('id');

            $table->unsignedBigInteger('ayudas_id')->nullable()->index();
            $table->string('product_name')->nullable();
            $table->string('stripe_product_id')->nullable()->index();
            $table->decimal('price', 10, 2)->nullable();
            $table->string('currency', 10)->default('eur');
            $table->enum('payment_type', ['monthly', 'annual', 'one_time'])->default('annual');

            $table->timestamps();

            // Clave foránea que apunta a la tabla ayudas
            $table->foreign('ayudas_id')->references('id')->on('ayudas')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropForeign(['ayudas_id']);
        });

        Schema::dropIfExists('products');
    }
}
