<?php

namespace App\Console\Commands;

use App\Services\EvaluadorAyudaService;
use Illuminate\Console\Command;

// to make this work, use  `php artisan ayuda:test <user_id> <ayuda_id>`

class TestEvaluadorAyuda extends Command
{
    protected $signature = 'ayuda:test {user_id} {ayuda_id}';

    protected $description = 'Evalúa si un usuario es beneficiario de una ayuda específica y muestra resultados detallados.';

    public function handle()
    {
        $userId = (int) $this->argument('user_id');
        $ayudaId = (int) $this->argument('ayuda_id');

        $this->info("🔍 Evaluando si el usuario $userId es beneficiario de la ayuda $ayudaId...");

        $servicio = new EvaluadorAyudaService;
        $resultado = $servicio->evaluarParaTester($ayudaId, $userId);

        $this->info('✅ Resultado global: '.($resultado['es_beneficiario'] ? 'BENEFICIARIO ✅' : 'NO BENEFICIARIO ❌'));
        $this->line('📋 Detalles de evaluación:');

        foreach ($resultado['detalles'] as $detalle) {
            $estado = ($detalle['resultado'] ?? '') ?: (($detalle['cumple'] ?? false) ? '✅ CUMPLE' : '❌ NO CUMPLE');
            $this->line(($detalle['cumple'] ?? false ? '✔️' : '❌').' Regla: '.($detalle['descripcion'] ?? 'Sin descripción'));

            if (! empty($detalle['detalles']) && is_array($detalle['detalles'])) {
                foreach ($detalle['detalles'] as $sub) {
                    if (is_array($sub)) {
                        $msg = [];
                        if (isset($sub['pregunta_id'])) {
                            $msg[] = 'pregunta_id='.$sub['pregunta_id'];
                        }
                        if (isset($sub['respuesta'])) {
                            $msg[] = 'respuesta='.json_encode($sub['respuesta']);
                        }
                        if (isset($sub['operador'])) {
                            $msg[] = 'operador='.$sub['operador'];
                        }
                        if (isset($sub['valor_esperado'])) {
                            $msg[] = 'valor_esperado='.json_encode($sub['valor_esperado']);
                        }
                        if (! empty($msg)) {
                            $this->line('    · '.implode(' | ', $msg));
                        }
                    } elseif (is_string($sub)) {
                        $this->line('    · '.$sub);
                    }
                }
            }
        }

        if (! empty($resultado['razones_no_cumple'])) {
            $this->warn("\nRazones de no cumplimiento:");
            foreach ($resultado['razones_no_cumple'] as $razon) {
                $this->warn(' - '.$razon);
            }
        }

        if (! empty($resultado['condiciones_desconocidas'])) {
            $this->line("\nCondiciones desconocidas (faltan respuestas):");
            foreach ($resultado['condiciones_desconocidas'] as $c) {
                $this->line(' - '.$c);
            }
        }

        return 0;
    }
}
