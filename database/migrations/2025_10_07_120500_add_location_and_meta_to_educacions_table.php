<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('educacions', function (Blueprint $table) {
            if (! Schema::hasColumn('educacions', 'provincia_id')) {
                $table->unsignedBigInteger('provincia_id')->nullable()->after('descripcion');
                $table->foreign('provincia_id')->references('id')->on('provincia')->nullOnDelete();
            }
            if (! Schema::hasColumn('educacions', 'municipio_id')) {
                $table->unsignedBigInteger('municipio_id')->nullable()->after('provincia_id');
                $table->foreign('municipio_id')->references('id')->on('municipio')->nullOnDelete();
            }
            if (! Schema::hasColumn('educacions', 'ownership')) {
                $table->string('ownership')->nullable()->after('municipio_id');
            }
            if (! Schema::hasColumn('educacions', 'modality')) {
                $table->string('modality')->nullable()->after('ownership');
            }
            if (! Schema::hasColumn('educacions', 'is_official')) {
                $table->boolean('is_official')->nullable()->after('modality');
            }
            if (! Schema::hasColumn('educacions', 'is_enrolled')) {
                $table->boolean('is_enrolled')->nullable()->after('is_official');
            }

            $table->index('provincia_id');
            $table->index('municipio_id');
        });
    }

    public function down(): void
    {
        Schema::table('educacions', function (Blueprint $table) {
            if (Schema::hasColumn('educacions', 'is_enrolled')) {
                $table->dropColumn('is_enrolled');
            }
            if (Schema::hasColumn('educacions', 'is_official')) {
                $table->dropColumn('is_official');
            }
            if (Schema::hasColumn('educacions', 'modality')) {
                $table->dropColumn('modality');
            }
            if (Schema::hasColumn('educacions', 'ownership')) {
                $table->dropColumn('ownership');
            }
            if (Schema::hasColumn('educacions', 'municipio_id')) {
                $table->dropForeign(['municipio_id']);
                $table->dropIndex(['municipio_id']);
                $table->dropColumn('municipio_id');
            }
            if (Schema::hasColumn('educacions', 'provincia_id')) {
                $table->dropForeign(['provincia_id']);
                $table->dropIndex(['provincia_id']);
                $table->dropColumn('provincia_id');
            }
        });
    }
};
