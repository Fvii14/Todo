<?php

namespace App\Console\Commands;

use App\Events\EventContratacionCompleted;
use App\Models\Contratacion;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ActualizarDealstageTramitacion extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hubspot:actualizar-dealstage-tramitacion';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca contrataciones completas con período abierto y actualiza el dealstage a Tramitación en HubSpot';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('🔍 Buscando contrataciones completas con período abierto (estado OP1-Documentacion)...');

        $hoy = Carbon::today();

        // Buscar contrataciones que:
        // 1. Tengan estado OPx OP1-Documentacion (aún no pasaron a tramitación)
        // 2. Tengan todos los documentos y formularios completos
        // 3. La ayuda tenga fecha_inicio <= hoy (período abierto)
        $contrataciones = Contratacion::with(['ayuda', 'user', 'estadosContratacion'])
            ->whereHas('estadosContratacion', fn ($q) => $q->where('codigo', 'OP1-Documentacion'))
            ->whereHas('ayuda', function ($query) use ($hoy) {
                $query->whereNotNull('fecha_inicio')
                    ->where('fecha_inicio', '<=', $hoy->format('Y-m-d'));
            })
            ->get();

        $this->info("📊 Encontradas {$contrataciones->count()} contrataciones con período abierto");

        $procesadas = 0;
        $completas = 0;
        $errores = 0;

        foreach ($contrataciones as $contratacion) {
            try {
                // Verificar si la contratación está completamente lista (documentos + formularios)
                if ($contratacion->isCompleta()) {
                    $completas++;

                    $this->info("✅ Contratación {$contratacion->id} está completa - Disparando evento");

                    // Disparar el evento que actualizará el dealstage en HubSpot
                    event(new EventContratacionCompleted($contratacion));

                    $procesadas++;

                    Log::info('[Cronjob] EventContratacionCompleted disparado para contratación', [
                        'contratacion_id' => $contratacion->id,
                        'user_id' => $contratacion->user_id,
                        'ayuda_id' => $contratacion->ayuda_id,
                        'fecha_inicio' => $contratacion->ayuda->fecha_inicio?->format('Y-m-d'),
                    ]);
                } else {
                    $this->line("⏳ Contratación {$contratacion->id} aún no está completa");
                }
            } catch (\Exception $e) {
                $errores++;
                $this->error("❌ Error procesando contratación {$contratacion->id}: {$e->getMessage()}");

                Log::error('[Cronjob] Error al procesar contratación en actualizar-dealstage-tramitacion', [
                    'contratacion_id' => $contratacion->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->info("\n📈 Resumen:");
        $this->info("   - Contrataciones procesadas: {$procesadas}");
        $this->info("   - Contrataciones completas encontradas: {$completas}");
        $this->info("   - Errores: {$errores}");

        return 0;
    }
}
