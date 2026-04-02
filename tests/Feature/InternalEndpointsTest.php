<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\AyudaSolicitada;
use App\Models\Contratacion;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InternalEndpointsTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_rocket_rejects_missing_secret(): void
    {
        $response = $this->postJson('/api/internal/dashboard-rocket');

        $response->assertStatus(403);
    }

    public function test_dashboard_rocket_rejects_wrong_secret(): void
    {
        $response = $this->postJson('/api/internal/dashboard-rocket', [], [
            'X-Internal-Secret' => 'wrong-secret',
        ]);

        $response->assertStatus(403);
    }

    public function test_dashboard_rocket_returns_stats_with_valid_secret(): void
    {
        config(['app.env' => 'testing']);
        putenv('DASHBOARD_SECRET=test-dashboard-secret');

        User::factory()->count(3)->create(['is_admin' => false]);
        User::factory()->create(['is_admin' => true]);

        $response = $this->postJson('/api/internal/dashboard-rocket', [], [
            'X-Internal-Secret' => 'test-dashboard-secret',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'status',
                'usuarios',
                'usuariosConBankflip',
                'usuariosSinBankflip',
                'ayudasSolicitidas',
                'contrataciones',
            ])
            ->assertJson(['status' => 'ok']);

        $this->assertEquals(3, $response->json('usuarios'));
    }

    public function test_mail_scheduler_rejects_missing_secret(): void
    {
        $response = $this->postJson('/api/internal/mail-scheduler');

        $response->assertStatus(403);
    }

    public function test_mail_scheduler_rejects_wrong_secret(): void
    {
        $response = $this->postJson('/api/internal/mail-scheduler', [], [
            'X-Internal-Secret' => 'wrong-secret',
        ]);

        $response->assertStatus(403);
    }

    public function test_bajadas_inactividad_rejects_missing_secret(): void
    {
        $response = $this->postJson('/api/internal/bajadas-inactividad');

        $response->assertStatus(403);
    }

    public function test_bajadas_inactividad_rejects_wrong_secret(): void
    {
        $response = $this->postJson('/api/internal/bajadas-inactividad', [], [
            'X-Internal-Secret' => 'wrong-secret',
        ]);

        $response->assertStatus(403);
    }

    public function test_obtener_firma_usuario_rejects_missing_secret(): void
    {
        $response = $this->postJson('/api/internal/obtener-firma-usuario', [
            'user_id' => 1,
        ]);

        $response->assertStatus(403);
    }

    public function test_obtener_firma_conviviente_rejects_missing_secret(): void
    {
        $response = $this->postJson('/api/internal/obtener-firma-conviviente', [
            'user_id' => 1,
            'conviviente_index' => 1,
        ]);

        $response->assertStatus(403);
    }
}
