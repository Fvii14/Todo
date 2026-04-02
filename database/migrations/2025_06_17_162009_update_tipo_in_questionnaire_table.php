<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class UpdateTipoInQuestionnaireTable extends Migration
{
    public function up()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            if (Schema::hasColumn('questionnaires', 'pre_post')) {
                $table->dropColumn('pre_post');
            }

            $table->enum('tipo', ['pre', 'post', 'conviviente', 'arrendatario', 'collector'])->default('pre')->after('ayuda_id');
        });
    }

    public function down()
    {
        Schema::table('questionnaires', function (Blueprint $table) {
            $table->dropColumn('tipo');
            $table->string('pre_post')->nullable()->after('ayuda_id'); // Restaurar si es necesario
        });
    }
}
