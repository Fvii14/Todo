<?php

namespace App\Console\Commands;

use App\Models\Answer;
use App\Models\Ayuda;
use App\Models\Ccaa;
use App\Models\PosibleBeneficiario;
use App\Models\Question;
use App\Models\User;
use App\Services\EvaluadorAyudaService;
use Illuminate\Console\Command;

class EvaluarPosiblesBeneficiarios extends Command
{
    /**
     * Firma del comando
     *
     * @var string
     */
    protected $signature = 'marketing:evaluar-beneficiarios {ayuda_id : ID de la ayuda a evaluar} {--chunk=100 : Número de usuarios a procesar por lote}';

    /**
     * Descripción del comando
     *
     * @var string
     */
    protected $description = 'Evalúa todos los usuarios para determinar posibles beneficiarios de una ayuda específica';

    protected EvaluadorAyudaService $evaluadorService;

    public function __construct()
    {
        parent::__construct();
        $this->evaluadorService = new EvaluadorAyudaService;
    }

    /**
     * Evaluar posibles beneficiarios para una ayuda específica
     */
    public function handle()
    {
        $ayudaId = (int) $this->argument('ayuda_id');
        $chunkSize = (int) $this->option('chunk');

        // Validar que la ayuda existe
        $ayuda = Ayuda::find($ayudaId);
        if (! $ayuda) {
            $this->error("❌ No se encontró la ayuda con ID: {$ayudaId}");

            return 1;
        }

        $this->info("🔍 Evaluando posibles beneficiarios para: {$ayuda->nombre_ayuda} (ID: {$ayudaId})");

        // Verificar si la ayuda tiene restricción por CCAA
        $ccaaAyudaId = $ayuda->ccaa_id;
        $ccaaAyuda = null;
        if ($ccaaAyudaId) {
            $ccaaAyuda = Ccaa::find($ccaaAyudaId);
            $this->info("📍 Ayuda restringida a: {$ccaaAyuda->nombre_ccaa}");
        } else {
            $this->info('📍 Ayuda sin restricción de CCAA (aplicable a todas)');
        }
        $this->newLine();

        // Limpiar resultados anteriores para esta ayuda
        $this->info('🧹 Limpiando resultados anteriores...');
        PosibleBeneficiario::where('ayuda_id', $ayudaId)->delete();
        $this->info('✅ Limpieza completada');
        $this->newLine();

        // Obtener IDs de preguntas necesarias
        $nombreQuestionId = Question::where('slug', 'nombre_completo')->value('id');
        $telefonoQuestionId = Question::where('slug', 'telefono')->value('id');
        $ccaaQuestionId = Question::where('slug', 'comunidad_autonoma')->value('id');

        // Construir query base de usuarios
        $queryUsuarios = User::query();

        // Solo incluir usuarios que tienen CCAA definida
        $queryUsuarios->whereHas('answers', function ($q) use ($ccaaQuestionId) {
            $q->where('question_id', $ccaaQuestionId)
                ->whereNull('conviviente_id')
                ->whereNotNull('answer')
                ->where('answer', '!=', '');
        });

        // Si la ayuda tiene restricción por CCAA (ayuda autonómica), filtrar usuarios por esa CCAA específica
        // Si la ayuda NO tiene ccaa_id (ayuda estatal), no se filtra por CCAA
        if ($ccaaAyudaId && $ccaaAyuda) {
            $queryUsuarios->whereHas('answers', function ($q) use ($ccaaQuestionId, $ccaaAyudaId, $ccaaAyuda) {
                $q->where('question_id', $ccaaQuestionId)
                    ->whereNull('conviviente_id')
                    ->where(function ($subQ) use ($ccaaAyudaId, $ccaaAyuda) {
                        // Buscar por ID numérico
                        $subQ->where('answer', $ccaaAyudaId)
                            // O buscar por nombre de CCAA (por si está guardado como texto)
                            ->orWhere('answer', 'like', '%'.$ccaaAyuda->nombre_ccaa.'%');
                    });
            });
        }
        // Si no tiene ccaa_id, es una ayuda estatal y se procesan usuarios de todas las CCAA

        // Excluir usuarios que ya han contratado esta ayuda
        $queryUsuarios->whereDoesntHave('contrataciones', function ($q) use ($ayudaId) {
            $q->where('ayuda_id', $ayudaId);
        });

        // Excluir usuarios que ya tienen registro en ayudas_solicitadas para esta ayuda
        $queryUsuarios->whereDoesntHave('ayudasSolicitadas', function ($q) use ($ayudaId) {
            $q->where('ayuda_id', $ayudaId);
        });

        // Contador de resultados
        $totalUsuarios = $queryUsuarios->count();
        $procesados = 0;
        $posiblesBeneficiarios = 0;

        $this->info("📊 Total de usuarios a procesar: {$totalUsuarios}");
        if ($ccaaAyudaId) {
            $this->info("   (Filtrados por CCAA: {$ccaaAyuda->nombre_ccaa})");
        }
        $this->info('   (Excluyendo usuarios que ya contrataron o solicitaron esta ayuda)');
        $this->info("📦 Tamaño de lote: {$chunkSize}");
        $this->newLine();

        // Barra de progreso
        $bar = $this->output->createProgressBar($totalUsuarios);
        $bar->start();

        // Procesar usuarios en chunks (con filtro de CCAA si aplica)
        $queryUsuarios->chunk($chunkSize, function ($users) use ($ayudaId, $nombreQuestionId, $telefonoQuestionId, $ccaaQuestionId, &$procesados, &$posiblesBeneficiarios, $bar) {
            foreach ($users as $user) {
                try {
                    // Evaluar si es posible beneficiario
                    $esPosible = $this->evaluadorService->posiblesAyudas($ayudaId, $user->id);

                    if ($esPosible) {
                        // Obtener datos del usuario
                        // Nombre: primero desde answers, si no existe desde users.name
                        $nombreCompleto = $this->obtenerRespuesta($user->id, $nombreQuestionId);
                        if (empty($nombreCompleto)) {
                            $nombreCompleto = $user->name;
                        }
                        $email = $user->email;
                        $telefono = $this->obtenerRespuesta($user->id, $telefonoQuestionId);
                        $ccaa = $this->obtenerCCAA($user->id, $ccaaQuestionId);

                        PosibleBeneficiario::create([
                            'ayuda_id' => $ayudaId,
                            'user_id' => $user->id,
                            'nombre_completo' => $nombreCompleto,
                            'email' => $email,
                            'telefono' => $telefono,
                            'ccaa' => $ccaa,
                        ]);
                        $posiblesBeneficiarios++;
                    }

                    $procesados++;
                    $bar->advance();
                } catch (\Exception $e) {
                    $this->newLine();
                    $this->warn("⚠️  Error procesando usuario {$user->id}: {$e->getMessage()}");
                    $procesados++;
                    $bar->advance();
                }
            }
        });

        $bar->finish();
        $this->newLine(2);

        // Resumen
        $this->info('✅ Procesamiento completado');
        $this->table(
            ['Métrica', 'Valor'],
            [
                ['Total usuarios procesados', $procesados],
                ['Posibles beneficiarios encontrados', $posiblesBeneficiarios],
                ['Porcentaje', $procesados > 0 ? number_format(($posiblesBeneficiarios / $procesados) * 100, 2).'%' : '0%'],
            ]
        );

        return 0;
    }

    /**
     * Obtiene una respuesta de un usuario para una pregunta específica
     */
    private function obtenerRespuesta(int $userId, ?int $questionId): ?string
    {
        if (! $questionId) {
            return null;
        }

        $answer = Answer::where('user_id', $userId)
            ->where('question_id', $questionId)
            ->whereNull('conviviente_id')
            ->value('answer');

        if (! $answer) {
            return null;
        }

        // Intentar decodificar JSON
        $decoded = json_decode($answer, true);
        if (json_last_error() === JSON_ERROR_NONE && $decoded !== null) {
            return is_array($decoded) ? implode(', ', $decoded) : (string) $decoded;
        }

        return $answer;
    }

    /**
     * Obtiene el nombre de la CCAA desde la respuesta del usuario
     */
    private function obtenerCCAA(int $userId, ?int $ccaaQuestionId): ?string
    {
        if (! $ccaaQuestionId) {
            return null;
        }

        $answer = Answer::where('user_id', $userId)
            ->where('question_id', $ccaaQuestionId)
            ->whereNull('conviviente_id')
            ->value('answer');

        if (! $answer) {
            return null;
        }

        // Si es numérico, buscar en la tabla ccaa
        if (is_numeric($answer)) {
            $ccaa = Ccaa::find($answer);

            return $ccaa ? $ccaa->nombre_ccaa : null;
        }

        // Si ya es texto, devolverlo
        return $answer;
    }
}
