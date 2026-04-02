<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            if (! Schema::hasColumn('contrataciones', 'subfase')) {
                $table->string('subfase', 255)->collation('utf8mb4_unicode_ci')->nullable()->after('fase');
            }
        });

        Schema::table('contrataciones', function (Blueprint $table) {
            DB::statement('
                ALTER TABLE contrataciones 
                MODIFY subfase VARCHAR(255) 
                CHARACTER SET utf8mb4 
                COLLATE utf8mb4_unicode_ci NULL
            ');

            if (! $this->indexExists('contrataciones', 'contrataciones_subfase_index')) {
                $table->index('subfase', 'contrataciones_subfase_index');
            }

            if (! $this->foreignKeyExists('contrataciones', 'contrataciones_subfase_fk')) {
                DB::statement('
                    ALTER TABLE contrataciones
                    ADD CONSTRAINT contrataciones_subfase_fk
                    FOREIGN KEY (subfase) REFERENCES subfase(slug)
                    ON UPDATE CASCADE
                    ON DELETE SET NULL');
            }
        });
    }

    public function down(): void
    {
        Schema::table('contrataciones', function (Blueprint $table) {
            if ($this->foreignKeyExists('contrataciones', 'contrataciones_subfase_fk')) {
                $table->dropForeign('contrataciones_subfase_fk');
            }
            if ($this->indexExists('contrataciones', 'contrataciones_subfase_index')) {
                $table->dropIndex('contrataciones_subfase_index');
            }
            if (Schema::hasColumn('contrataciones', 'subfase')) {
                $table->dropColumn('subfase');
            }
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        $res = DB::select("SHOW INDEX FROM `{$table}` WHERE Key_name = ?", [$index]);

        return ! empty($res);
    }

    private function foreignKeyExists(string $table, string $fkName): bool
    {
        $dbName = DB::getDatabaseName();
        $res = DB::select("
            SELECT CONSTRAINT_NAME
            FROM information_schema.TABLE_CONSTRAINTS
            WHERE TABLE_SCHEMA = ? AND TABLE_NAME = ? AND CONSTRAINT_NAME = ? AND CONSTRAINT_TYPE = 'FOREIGN KEY'
        ", [$dbName, $table, $fkName]);

        return ! empty($res);
    }
};
