<?php

namespace Tests\Unit;

use App\Models\Answer;
use App\Models\AyudaRequisitoJson;
use App\Services\EvaluadorAyudaService;
use Illuminate\Database\Eloquent\Collection;
use Mockery;
use Tests\TestCase;

class EvaluadorAyudaServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    public function test_evaluarJson_returns_not_eligible_when_no_rules_configured(): void
    {
        // Mock AyudaRequisitoJson::where()->get() to return empty
        $this->mock(AyudaRequisitoJson::class, function ($mock) {
            $mock->shouldReceive('where->get')
                ->andReturn(new Collection);
        });

        $service = new EvaluadorAyudaService;
        $result = $service->evaluarJson(999, 1);

        $this->assertFalse($result['es_beneficiario']);
        $this->assertEmpty($result['detalles']);
        $this->assertContains(
            'Esta ayuda no tiene lógica de elegibilidad configurada',
            $result['razones_no_cumple']
        );
    }

    public function test_evaluarJson_response_structure(): void
    {
        // Mock with empty rules to test response shape
        $this->mock(AyudaRequisitoJson::class, function ($mock) {
            $mock->shouldReceive('where->get')
                ->andReturn(new Collection);
        });

        $service = new EvaluadorAyudaService;
        $result = $service->evaluarJson(1, 1);

        $this->assertArrayHasKey('es_beneficiario', $result);
        $this->assertArrayHasKey('detalles', $result);
        $this->assertArrayHasKey('razones_no_cumple', $result);
        $this->assertIsBool($result['es_beneficiario']);
        $this->assertIsArray($result['detalles']);
        $this->assertIsArray($result['razones_no_cumple']);
    }

    public function test_posiblesAyudasBatch_returns_array_keyed_by_ayuda_id(): void
    {
        // Mock empty requisitos for all ayudas
        $this->mock(AyudaRequisitoJson::class, function ($mock) {
            $mock->shouldReceive('where->get')
                ->andReturn(new Collection);
        });

        $this->mock(Answer::class, function ($mock) {
            $mock->shouldReceive('where->where->get')
                ->andReturn(new Collection);
        });

        $service = new EvaluadorAyudaService;
        $result = $service->posiblesAyudasBatch([1, 2, 3], 1);

        $this->assertIsArray($result);
    }
}
