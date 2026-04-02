<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ayuda_requisitos', function (Blueprint $table) {
            $table->enum('tipo_comparacion', ['igual', 'mayor', 'menor', 'entre', 'in', 'no_in', 'bool', 'custom'])->default('igual')->after('question_id');
            $table->string('valor1')->nullable()->after('tipo_comparacion');
            $table->string('valor2')->nullable()->after('valor1');
            $table->boolean('obligatorio')->default(true)->after('valor2');
            $table->unsignedBigInteger('condicion_previa_id')->nullable()->after('obligatorio');
            $table->string('condicion_valor_esperado')->nullable()->after('condicion_previa_id');
            $table->json('excepcion')->nullable()->after('condicion_valor_esperado');
            $table->text('observaciones')->nullable()->after('excepcion');

            $table->foreign('condicion_previa_id')->references('id')->on('ayuda_requisitos')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('ayuda_requisitos', function (Blueprint $table) {
            $table->dropForeign(['condicion_previa_id']);
            $table->dropColumn([
                'tipo_comparacion',
                'valor1',
                'valor2',
                'obligatorio',
                'condicion_previa_id',
                'condicion_valor_esperado',
                'excepcion',
                'observaciones',
            ]);
        });
    }
};
