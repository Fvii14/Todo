<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

return new class extends Migration
{
    private $stats = [
        'encontradas' => 0,
        'actualizadas' => 0,
        'tareas_insertadas' => 0,
        'omitidas' => 0,
        'no_mapeadas' => 0,
    ];

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Log::info('Iniciando migración de flujo viejo a nuevo');

        // 1. Modificar ENUMs a VARCHAR si es necesario
        $this->modificarEnumsAVarchar();

        // 2. Upsert tareas base necesarias
        $this->upsertTareasBase();

        // 3. Upsert opciones de tareas necesarias
        $this->upsertOpcionesTareas();

        // 4. Procesar contrataciones en lotes
        $this->procesarContrataciones();

        // 5. Log de estadísticas finales
        $this->logEstadisticas();
    }

    /**
     * Modificar columnas ENUM a VARCHAR si es necesario
     */
    private function modificarEnumsAVarchar(): void
    {
        Log::info('Verificando tipos de columnas en contrataciones');

        // Verificar si estado es ENUM
        $estadoType = DB::select("
            SELECT DATA_TYPE 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'contrataciones' 
            AND COLUMN_NAME = 'estado'
        ");

        if (! empty($estadoType) && $estadoType[0]->DATA_TYPE === 'enum') {
            Log::info('Modificando columna estado de ENUM a VARCHAR(191)');
            DB::statement('ALTER TABLE contrataciones MODIFY estado VARCHAR(191) NULL');
        }

        // Verificar si fase es ENUM
        $faseType = DB::select("
            SELECT DATA_TYPE 
            FROM information_schema.COLUMNS 
            WHERE TABLE_SCHEMA = DATABASE() 
            AND TABLE_NAME = 'contrataciones' 
            AND COLUMN_NAME = 'fase'
        ");

        if (! empty($faseType) && $faseType[0]->DATA_TYPE === 'enum') {
            Log::info('Modificando columna fase de ENUM a VARCHAR(191)');
            DB::statement('ALTER TABLE contrataciones MODIFY fase VARCHAR(191) NULL');
        }
    }

    /**
     * Upsert tareas base necesarias
     */
    private function upsertTareasBase(): void
    {
        Log::info('Upsert tareas base necesarias');

        $tareasBase = [
            'solicitud' => 'Solicitud',
            'cotejo' => 'Cotejo',
            'elaborar' => 'Elaborar...',
            'tramitacion' => 'Tramitación',
            'registro' => 'Registro',
        ];

        foreach ($tareasBase as $slug => $nombre) {
            DB::table('tareas')->updateOrInsert(
                ['slug' => $slug],
                [
                    'nombre' => $nombre,
                    'descripcion' => "Tarea de {$nombre}",
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
        }
    }

    /**
     * Upsert opciones de tareas necesarias
     */
    private function upsertOpcionesTareas(): void
    {
        Log::info('Upsert opciones de tareas necesarias');

        $opcionesTareas = [
            // Para solicitud
            'solicitud' => ['documentacion', 'seguimiento', 'subsanacion', 'justificacion', 'cobro', 'resena', 'feedback'],
            // Para cotejo
            'cotejo' => ['documentacion', 'seguimiento', 'subsanacion', 'justificacion', 'cobro'],
            // Para elaborar
            'elaborar' => ['documentacion', 'seguimiento', 'subsanacion', 'justificacion'],
            // Para tramitacion
            'tramitacion' => ['solicitud', 'subsanacion', 'justificacion'],
            // Para registro
            'registro' => ['subsanacion', 'resolucion', 'pago'],
        ];

        foreach ($opcionesTareas as $tareaSlug => $opciones) {
            foreach ($opciones as $opcionNombre) {
                DB::table('opciones_tareas')->updateOrInsert(
                    [
                        'tarea' => $tareaSlug,
                        'nombre' => $opcionNombre,
                    ],
                    [
                        'descripcion' => "Opción de {$opcionNombre} para {$tareaSlug}",
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]
                );
            }
        }
    }

    /**
     * Procesar contrataciones en lotes
     */
    private function procesarContrataciones(): void
    {
        Log::info('Iniciando procesamiento de contrataciones en lotes');

        $chunkSize = 500;
        $offset = 0;

        do {
            $contrataciones = DB::table('contrataciones')
                ->select('id', 'estado', 'fase')
                ->orderBy('id')
                ->offset($offset)
                ->limit($chunkSize)
                ->get();

            if ($contrataciones->isEmpty()) {
                break;
            }

            DB::transaction(function () use ($contrataciones) {
                foreach ($contrataciones as $contratacion) {
                    $this->procesarContratacion($contratacion);
                }
            });

            $offset += $chunkSize;
            $this->stats['encontradas'] += $contrataciones->count();

        } while ($contrataciones->count() === $chunkSize);
    }

    /**
     * Procesar una contratación individual
     */
    private function procesarContratacion($contratacion): void
    {
        $estadoNormalizado = $this->normalizarString($contratacion->estado);
        $faseNormalizada = $this->normalizarString($contratacion->fase);

        // Debug: log de normalización para las primeras 5 contrataciones
        if ($contratacion->id <= 5) {
            Log::info("Debug ID {$contratacion->id}: estado='{$contratacion->estado}' -> '{$estadoNormalizado}', fase='{$contratacion->fase}' -> '{$faseNormalizada}'");
        }

        $mapeo = $this->mapearViejoANuevo($estadoNormalizado, $faseNormalizada);

        if (! $mapeo) {
            $this->stats['no_mapeadas']++;
            Log::warning("No se pudo mapear contratación ID {$contratacion->id}: estado='{$contratacion->estado}' -> '{$estadoNormalizado}', fase='{$contratacion->fase}' -> '{$faseNormalizada}'");

            return;
        }

        // Actualizar contratación
        DB::table('contrataciones')
            ->where('id', $contratacion->id)
            ->update([
                'estado' => $mapeo['estado_nuevo'],
                'fase' => $mapeo['fase_nueva'],
                'updated_at' => now(),
            ]);

        $this->stats['actualizadas']++;

        // Insertar tarea en contratacion_tareas si es necesario
        if ($mapeo['tarea'] && $mapeo['opcion_nombre']) {
            $this->insertarTareaContratacion($contratacion->id, $mapeo['tarea'], $mapeo['opcion_nombre']);
        }
    }

    /**
     * Insertar tarea en contratacion_tareas
     */
    private function insertarTareaContratacion($contratacionId, $tareaSlug, $opcionNombre): void
    {
        // Obtener ID de la opción
        $opcionTarea = DB::table('opciones_tareas')
            ->where('tarea', $tareaSlug)
            ->where('nombre', $opcionNombre)
            ->first();

        if (! $opcionTarea) {
            Log::warning("No se encontró opción '{$opcionNombre}' para tarea '{$tareaSlug}'");

            return;
        }

        // Verificar si ya existe (idempotencia)
        $existe = DB::table('contratacion_tareas')
            ->where('contratacion_id', $contratacionId)
            ->where('tarea', $tareaSlug)
            ->where('opcion_tarea', $opcionTarea->id)
            ->exists();

        if ($existe) {
            $this->stats['omitidas']++;

            return;
        }

        // Insertar nueva tarea
        DB::table('contratacion_tareas')->insert([
            'contratacion_id' => $contratacionId,
            'tarea' => $tareaSlug,
            'opcion_tarea' => $opcionTarea->id,
            'estado_tarea' => 'pendiente',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->stats['tareas_insertadas']++;
    }

    /**
     * Mapear estado y fase viejo a nuevo
     */
    private function mapearViejoANuevo($estado, $fase): ?array
    {
        $mapeo = [
            'documentacion' => [
                'solicitud' => ['estado_nuevo' => 'documentacion', 'fase_nueva' => null, 'tarea' => 'solicitud', 'opcion_nombre' => 'documentacion'],
                'cotejo' => ['estado_nuevo' => 'documentacion', 'fase_nueva' => null, 'tarea' => 'cotejo', 'opcion_nombre' => 'documentacion'],
                'validacion' => ['estado_nuevo' => 'documentacion', 'fase_nueva' => null, 'tarea' => 'cotejo', 'opcion_nombre' => 'documentacion'],
                'elaborar' => ['estado_nuevo' => 'documentacion', 'fase_nueva' => null, 'tarea' => 'elaborar', 'opcion_nombre' => 'documentacion'],
                'cualquiera' => ['estado_nuevo' => 'documentacion', 'fase_nueva' => null, 'tarea' => 'solicitud', 'opcion_nombre' => 'documentacion'],
            ],
            'pendiente_apertura' => [
                'solicitud' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'en_seguimiento', 'tarea' => 'solicitud', 'opcion_nombre' => 'seguimiento'],
                'cotejo' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'en_seguimiento', 'tarea' => 'cotejo', 'opcion_nombre' => 'seguimiento'],
                'validacion' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'en_seguimiento', 'tarea' => 'cotejo', 'opcion_nombre' => 'seguimiento'],
                'elaborar' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'en_seguimiento', 'tarea' => 'elaborar', 'opcion_nombre' => 'seguimiento'],
                'cualquiera' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'en_seguimiento', 'tarea' => null, 'opcion_nombre' => null],
            ],
            'tramitacion' => [
                'en_tramitacion' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => null, 'tarea' => 'tramitacion', 'opcion_nombre' => 'solicitud'],
                'presentada' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'presentada', 'tarea' => null, 'opcion_nombre' => null],
                'cualquiera' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => null, 'tarea' => 'tramitacion', 'opcion_nombre' => 'solicitud'],
            ],
            'subsanacion' => [
                'solicitud' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'presentada', 'tarea' => 'solicitud', 'opcion_nombre' => 'subsanacion'],
                'cotejo' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'presentada', 'tarea' => 'cotejo', 'opcion_nombre' => 'subsanacion'],
                'validacion' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'presentada', 'tarea' => 'cotejo', 'opcion_nombre' => 'subsanacion'],
                'elaborar' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'presentada', 'tarea' => 'elaborar', 'opcion_nombre' => 'subsanacion'],
                'en_tramitacion' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'presentada', 'tarea' => 'tramitacion', 'opcion_nombre' => 'subsanacion'],
                'presentada' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'presentada', 'tarea' => null, 'opcion_nombre' => null],
            ],
            'concesion' => [
                'cualquiera' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => null, 'opcion_nombre' => null],
            ],
            'concedida' => [
                'cualquiera' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => null, 'opcion_nombre' => null],
            ],
            'justificacion' => [
                'solicitud' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => 'solicitud', 'opcion_nombre' => 'justificacion'],
                'cotejo' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => 'cotejo', 'opcion_nombre' => 'justificacion'],
                'validacion' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => 'cotejo', 'opcion_nombre' => 'justificacion'],
                'elaborar' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => 'elaborar', 'opcion_nombre' => 'justificacion'],
                'en_tramitacion' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => 'tramitacion', 'opcion_nombre' => 'justificacion'],
                'presentada' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => null, 'opcion_nombre' => null],
            ],
            // Estados adicionales encontrados en los datos
            'finalizada' => [
                'cualquiera' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => null, 'opcion_nombre' => null],
            ],
            'rechazada' => [
                'cualquiera' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'rechazada', 'tarea' => null, 'opcion_nombre' => null],
            ],
            'resolucion' => [
                'cualquiera' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => 'registro', 'opcion_nombre' => 'resolucion'],
            ],
            'tramitación' => [ // Con tilde
                'en_tramitacion' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'seguimiento', 'tarea' => 'tramitacion', 'opcion_nombre' => 'solicitud'],
                'presentada' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => 'presentada', 'tarea' => null, 'opcion_nombre' => null],
                'cualquiera' => ['estado_nuevo' => 'tramitacion', 'fase_nueva' => null, 'tarea' => 'tramitacion', 'opcion_nombre' => 'solicitud'],
            ],
            'cierre' => [
                'cualquiera' => ['estado_nuevo' => 'cierre', 'fase_nueva' => 'resolucion', 'tarea' => 'registro', 'opcion_nombre' => 'resolucion'],
            ],
        ];

        // Buscar mapeo exacto
        if (isset($mapeo[$estado][$fase])) {
            return $mapeo[$estado][$fase];
        }

        // Buscar mapeo con "cualquiera"
        if (isset($mapeo[$estado]['cualquiera'])) {
            return $mapeo[$estado]['cualquiera'];
        }

        return null;
    }

    /**
     * Normalizar string (minúsculas, sin tildes, espacios a guiones bajos)
     */
    private function normalizarString(?string $str): string
    {
        if (! $str) {
            return '';
        }

        // Convertir a minúsculas
        $str = strtolower(trim($str));

        // Quitar tildes
        $str = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'u'],
            $str
        );

        // Convertir espacios y guiones a guiones bajos
        $str = preg_replace('/[\s\-]+/', '_', $str);

        // Casos especiales
        $str = str_replace('pendiente_apertura', 'pendiente_apertura', $str);
        $str = str_replace('en_tramitacion', 'en_tramitacion', $str);
        $str = str_replace('cotejo/validacion', 'cotejo', $str);

        // Mapeos específicos encontrados en los datos
        $mapeosEspeciales = [
            'tramitación' => 'tramitacion',  // Con tilde
            'finalizada' => 'finalizada',
            'rechazada' => 'rechazada',
            'resolución' => 'resolucion',    // Con tilde
            'cierre' => 'cierre',
            'documentacion' => 'documentacion',
        ];

        foreach ($mapeosEspeciales as $original => $normalizado) {
            if ($str === $original) {
                $str = $normalizado;
                break;
            }
        }

        return $str;
    }

    /**
     * Log de estadísticas finales
     */
    private function logEstadisticas(): void
    {
        Log::info('Estadísticas de migración:', $this->stats);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No implementar reversión completa por pérdida de contexto
        Log::info('Reversión de migración no implementada - pérdida de contexto');
    }
};
