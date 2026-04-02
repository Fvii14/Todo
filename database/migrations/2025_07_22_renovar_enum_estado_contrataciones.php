<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('ALTER TABLE contrataciones MODIFY COLUMN estado VARCHAR(50)');

        DB::table('contrataciones')->where('estado', 'procesando')->update(['estado' => 'documentación']);
        DB::table('contrataciones')->where('estado', 'resolución definitiva')->update(['estado' => 'resolución']);
        DB::table('contrataciones')->where('estado', 'tramitando')->update(['estado' => 'tramitación']);
        DB::table('contrataciones')->where('estado', 'renunciada')->update(['estado' => 'rechazada']);

        DB::table('contrataciones')->whereNotIn('estado', [
            'concedida', 'rechazada', 'documentación', 'resolución', 'subsanación', 'tramitación', '',
        ])->update(['estado' => '']);

        DB::statement("ALTER TABLE contrataciones MODIFY COLUMN estado 
            ENUM('concedida', 'rechazada', 'documentación', 'resolución', 'subsanación', 'tramitación', '') DEFAULT ''");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('ALTER TABLE contrataciones MODIFY COLUMN estado VARCHAR(50)');

        DB::table('contrataciones')->where('estado', 'documentación')->update(['estado' => 'procesando']);
        DB::table('contrataciones')->where('estado', 'resolución')->update(['estado' => 'resolución definitiva']);
        DB::table('contrataciones')->where('estado', 'tramitación')->update(['estado' => 'tramitando']);
        DB::table('contrataciones')->where('estado', 'rechazada')->update(['estado' => 'renunciada']);

        DB::statement("ALTER TABLE contrataciones MODIFY COLUMN estado 
            ENUM(
                'procesando', 'tramitando', 'tramitada', 'concedida', 'rechazada',
                'documentación', 'resolución provisional', 'subsanación', 'resolución definitiva',
                'subsanación mediante reposición', 'lista de espera por insuficiencia de crédito',
                'ayuda aprobada', 'ayuda recibida', 'desistida', 'renunciada', 'devolución',
                'finalizada'
            ) DEFAULT 'procesando'");
    }
};
