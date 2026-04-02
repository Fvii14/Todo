<?php

namespace Tests\Feature;

use App\Services\EvaluadorAyudaService;
use Tests\TestCase;

class EvaluadorAyudaServiceTest extends TestCase
{
    // Test para comprobar de manera REAL una ayuda y un usuario
    // Funcionamiento en la terminal:  php artisan test --filter EvaluadorAyudaServiceTest
    public function test_evaluar_json_usuario_y_ayuda_real()
    {
        $ayudaId = 7;
        $userId = 276;

        $servicio = new EvaluadorAyudaService;
        $resultado = $servicio->evaluarJson($ayudaId, $userId);
        dump($resultado);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('es_beneficiario', $resultado);
        $this->assertArrayHasKey('detalles', $resultado);
    }
}
