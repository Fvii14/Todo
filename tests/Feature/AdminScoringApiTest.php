<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminScoringApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_scoring_index_returns_success(): void
    {
        $response = $this->getJson('/api/admin/scoring/');

        $response->assertSuccessful();
    }

    public function test_scoring_por_usuario_returns_data(): void
    {
        $user = User::factory()->create();

        $response = $this->getJson("/api/admin/scoring/usuario/{$user->id}");

        $response->assertSuccessful();
    }

    public function test_scoring_estadisticas_returns_success(): void
    {
        $response = $this->getJson('/api/admin/scoring/estadisticas/estadisticas');

        $response->assertSuccessful();
    }

    public function test_scoring_prioridad_returns_success(): void
    {
        $response = $this->getJson('/api/admin/scoring/prioridad/prioridad');

        $response->assertSuccessful();
    }

    public function test_scoring_filtrar_score_returns_success(): void
    {
        $response = $this->getJson('/api/admin/scoring/filtrar/score');

        $response->assertSuccessful();
    }

    public function test_scoring_filtrar_estado_returns_success(): void
    {
        $response = $this->getJson('/api/admin/scoring/filtrar/estado');

        $response->assertSuccessful();
    }

    public function test_scoring_estadisticas_tareas_crm_returns_success(): void
    {
        $response = $this->getJson('/api/admin/scoring/estadisticas-tareas-crm/estadisticas');

        $response->assertSuccessful();
    }
}
