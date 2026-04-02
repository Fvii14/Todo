<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 1) Añadir nueva columna
            $table->string('question_slug', 255)
                ->after('question_id')
                ->nullable(false);
        });

        // 2) Poblarla desde questions.slug
        DB::table('ayuda_datos')
            ->join('questions', 'ayuda_datos.question_id', '=', 'questions.id')
            ->update([
                'ayuda_datos.question_slug' => DB::raw('questions.slug'),
            ]);

        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 3) Eliminar FK y columna question_id
            $table->dropForeign(['question_id']);
            $table->dropColumn('question_id');
        });
    }

    public function down()
    {
        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 1) Recrear question_id
            $table->unsignedBigInteger('question_id')
                ->after('question_slug');
        });

        // 2) Repoblar question_id desde question_slug
        DB::table('ayuda_datos')
            ->join('questions', 'ayuda_datos.question_slug', '=', 'questions.slug')
            ->update([
                'ayuda_datos.question_id' => DB::raw('questions.id'),
            ]);

        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 3) Restaurar FK
            $table->foreign('question_id')
                ->references('id')->on('questions');
        });

        Schema::table('ayuda_datos', function (Blueprint $table) {
            // 4) Eliminar columna question_slug
            $table->dropColumn('question_slug');
        });
    }
};
