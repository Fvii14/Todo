<?php

namespace App\Console\Commands;

use App\Models\Contratacion;
use App\Models\EstadoContratacion;
use Illuminate\Console\Command;

class MigrarEstadosOpContrataciones extends Command
{
    /**
     * Firma del comando.
     *
     * Ejemplo de uso:
     *  php artisan contrataciones:migrar-estados-op
     *  php artisan contrataciones:migrar-estados-op --dry
     */
    protected $signature = 'contrataciones:migrar-estados-op {--dry : Solo mostrar qué se haría, sin guardar cambios}';

    /**
     * Descripción del comando.
     *
     * @var string
     */
    protected $description = 'Migra las contrataciones existentes al nuevo sistema de estados OPx a partir de estado/fase actuales';

    /**
     * Ejecutar el comando.
     */
    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry');

        $this->info('Iniciando migración de estados OPx para contrataciones...');
        if ($dryRun) {
            $this->warn('MODO DRY-RUN: no se guardarán cambios en la base de datos.');
        }

        // Cachear IDs de EstadoContratacion por código
        $estadosOp = EstadoContratacion::pluck('id', 'codigo');
        if ($estadosOp->isEmpty()) {
            $this->error('No hay registros en la tabla estados_contratacion. Ejecuta primero las migraciones.');

            return 1;
        }

        $totales = [
            'procesadas' => 0,
            'sin_mapeo' => 0,
            'mapeadas' => 0,
        ];

        Contratacion::chunkById(200, function ($contrataciones) use (&$totales, $estadosOp, $dryRun) {
            foreach ($contrataciones as $contratacion) {
                $totales['procesadas']++;

                $estado = $contratacion->estado;
                $fase = $contratacion->fase;

                $codigos = $this->mapearCodigosOp($estado, $fase);

                if (empty($codigos)) {
                    $totales['sin_mapeo']++;

                    continue;
                }

                // Traducir códigos OPx a IDs existentes en estados_contratacion
                $ids = collect($codigos)
                    ->filter(fn ($codigo) => isset($estadosOp[$codigo]))
                    ->map(fn ($codigo) => $estadosOp[$codigo])
                    ->values()
                    ->all();

                if (empty($ids)) {
                    $totales['sin_mapeo']++;

                    continue;
                }

                $totales['mapeadas']++;

                if ($dryRun) {
                    $this->line(sprintf(
                        '[DRY] Contratación #%d (estado=%s, fase=%s) -> %s',
                        $contratacion->id,
                        $estado ?? 'NULL',
                        $fase ?? 'NULL',
                        implode(', ', $codigos)
                    ));
                } else {
                    // No eliminamos estados previos, solo añadimos los nuevos sin duplicarlos
                    $contratacion->estadosContratacion()->syncWithoutDetaching($ids);
                }
            }
        });

        $this->info('Migración finalizada.');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Contrataciones procesadas', $totales['procesadas']],
                ['Con mapeo aplicado', $totales['mapeadas']],
                ['Sin mapeo (no se asignó OPx)', $totales['sin_mapeo']],
            ]
        );

        if ($dryRun) {
            $this->warn('Recuerda ejecutar sin --dry cuando verifiques que el mapeo es correcto.');
        }

        return 0;
    }

    /**
     * Mapea el par (estado, fase) actual a los códigos OPx definidos.
     *
     * IMPORTANTE: este mapa está basado en la tabla que hemos definido:
     *
     *  - estado = documentacion, fase = documentacion      -> OP1-Documentacion
     *  - estado = tramitacion,  fase = en_seguimiento      -> OP1-Tramitacion
     *  - estado = tramitacion,  fase = apertura            -> OP1-Tramitacion
     *  - estado = tramitacion,  fase = presentada          -> OP1-Tramitacion
     *  - estado = cierre,       fase = resolucion          -> OP1-Resolucion
     *  - estado = cierre,       fase = rechazada           -> OP1-Resolucion
     *
     *  Estados que "ya no sirven" (no generan OPx):
     *  - alegacion-aportacion, resolucion, subsanacion, justificacion,
     *    solicitud, renuncia, recurso-reposicion  (con fase = NULL)
     *
     * Si en el futuro quieres mapear alguno de estos a OPx, amplía aquí.
     */
    protected function mapearCodigosOp(?string $estado, ?string $fase): array
    {
        $estado = $estado ?? '';
        $fase = $fase ?? '';

        // Normalizamos a minúsculas por seguridad
        $estado = mb_strtolower($estado);
        $fase = mb_strtolower($fase);

        // Documentación: cualquier fase (incl. null) -> OP1-Documentacion
        if ($estado === 'documentacion') {
            return ['OP1-Documentacion'];
        }

        // Tramitación: cualquier fase
        if ($estado === 'tramitacion') {
            return ['OP1-Tramitacion'];
        }

        // Cierre: concedida o rechazada -> OP1-Resolucion
        if ($estado === 'cierre') {
            return ['OP1-Resolucion'];
        }

        // Subsanación, justificacion, solicitud, alegación... en curso -> OP1-Tramitacion
        if (in_array($estado, [
            'alegacion-aportacion',
            'subsanacion',
            'justificacion',
            'solicitud',
        ], true)) {
            return ['OP1-Tramitacion'];
        }

        // Renuncia, recurso, resolución (estado legacy) -> cierre lógico -> OP1-Resolucion
        if (in_array($estado, [
            'renuncia',
            'recurso-reposicion',
            'resolucion',
        ], true)) {
            return ['OP1-Resolucion'];
        }

        // Por defecto (ej. estado vacío o desconocido): OP1-Documentacion como fallback
        return ['OP1-Documentacion'];
    }
}
