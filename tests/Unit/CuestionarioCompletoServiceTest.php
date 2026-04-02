<?php

namespace Tests\Unit;

use App\Services\CuestionarioCompletoService;
use Tests\TestCase;

class CuestionarioCompletoServiceTest extends TestCase
{
    public function test_cuestionario_real_usuario_principal()
    {
        // Sustituye estos valores por IDs reales de tu base de datos
        $userId = 275; // ← ID real de un usuario con respuestas
        $questionnaireId = 85; // ← ID real del cuestionario que quieres probar

        $service = new CuestionarioCompletoService;
        $resultado = $service->usuarioPrincipalTieneCuestionarioCompleto($userId, $questionnaireId);

        dump('Resultado:', $resultado);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('completo', $resultado);
        $this->assertArrayHasKey('faltantes', $resultado);
    }

    public function test_cuestionario_real_conviviente()
    {
        $userId = 275;
        $questionnaireId = 47;

        $service = new CuestionarioCompletoService;
        $resultado = $service->convivientesTienenCuestionarioCompleto($userId, $questionnaireId);

        dump('Resultado Conviviente:', $resultado);

        $this->assertIsArray($resultado);
        $this->assertArrayHasKey('completo', $resultado);
        $this->assertArrayHasKey('faltantes_por_conviviente', $resultado);

        // Puedes hacer esta comprobación si quieres verificar el mensaje
        if (! $resultado['completo']) {
            $this->assertArrayHasKey('incompleto', $resultado['faltantes_por_conviviente']);
        }
    }
}
