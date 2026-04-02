<?php

namespace Tests\Unit;

use App\Models\Contratacion;
use App\Services\ContratacionEstadoService;
use App\Services\EstadoContratacionService;
use App\Services\EstadoService;
use Mockery;
use Tests\TestCase;

class ContratacionEstadoServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_cambiar_estado_with_valid_slug(): void
    {
        $estadoService = Mockery::mock(EstadoService::class);
        $estadoService->shouldReceive('isValidEstadoSlug')
            ->with('pagado')
            ->andReturn(true);

        $estadoContratacionService = Mockery::mock(EstadoContratacionService::class);

        $contratacion = Mockery::mock(Contratacion::class)->makePartial();
        $contratacion->estado = 'pendiente';
        $contratacion->fase = 'inicio';
        $contratacion->id = 1;
        $contratacion->shouldReceive('save')->once();

        $service = new ContratacionEstadoService($estadoService, $estadoContratacionService);

        // Mock HistorialActividad::create to avoid DB calls
        $this->mock(\App\Models\HistorialActividad::class, function ($mock) {
            $mock->shouldReceive('create')->once();
        });

        $result = $service->cambiarEstado($contratacion, 'pagado');

        $this->assertEquals('pagado', $result['estado']);
        $this->assertEquals('pendiente', $result['estado_anterior']);
        $this->assertEquals('inicio', $result['fase_anterior']);
    }

    public function test_cambiar_estado_with_invalid_slug_throws_exception(): void
    {
        $estadoService = Mockery::mock(EstadoService::class);
        $estadoService->shouldReceive('isValidEstadoSlug')
            ->with('estado_invalido')
            ->andReturn(false);

        $estadoContratacionService = Mockery::mock(EstadoContratacionService::class);

        $contratacion = Mockery::mock(Contratacion::class)->makePartial();
        $contratacion->id = 1;

        $service = new ContratacionEstadoService($estadoService, $estadoContratacionService);

        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage("El estado 'estado_invalido' no es válido");

        $service->cambiarEstado($contratacion, 'estado_invalido');
    }
}
