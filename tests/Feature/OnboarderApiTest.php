<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OnboarderApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_get_completed_returns_404_when_none_exist(): void
    {
        $response = $this->getJson('/api/onboarders/completed');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'No se encontró ningún onboarder completado',
            ]);
    }

    public function test_get_wizard_config_returns_404_for_nonexistent_wizard(): void
    {
        $response = $this->getJson('/api/onboarders/wizard-config/99999');

        $response->assertStatus(404);
    }

    public function test_save_answer_requires_valid_data(): void
    {
        $response = $this->postJson('/api/onboarders/save-answer', []);

        $response->assertStatus(422);
    }

    public function test_complete_section_requires_valid_data(): void
    {
        $response = $this->postJson('/api/onboarders/complete-section', []);

        $response->assertStatus(422);
    }

    public function test_complete_onboarder_requires_valid_data(): void
    {
        $response = $this->postJson('/api/onboarders/complete', []);

        $response->assertStatus(422);
    }

    public function test_add_conviviente_requires_valid_data(): void
    {
        $response = $this->postJson('/api/onboarders/add-conviviente', []);

        $response->assertStatus(422);
    }

    public function test_track_metric_requires_valid_data(): void
    {
        $response = $this->postJson('/api/onboarders/metrics', []);

        $response->assertStatus(422);
    }

    public function test_get_metrics_returns_404_for_nonexistent_onboarder(): void
    {
        $response = $this->getJson('/api/onboarders/99999/metrics');

        $response->assertStatus(404);
    }

    public function test_onboarder_metrics_index_returns_success(): void
    {
        $response = $this->getJson('/api/onboarders/onboarder-metrics');

        $response->assertSuccessful();
    }

    public function test_onboarder_metrics_section_stats_returns_success(): void
    {
        $response = $this->getJson('/api/onboarders/onboarder-metrics/section-stats');

        $response->assertSuccessful();
    }

    public function test_onboarder_metrics_abandonment_stats_returns_success(): void
    {
        $response = $this->getJson('/api/onboarders/onboarder-metrics/abandonment-stats');

        $response->assertSuccessful();
    }

    public function test_conviviente_builder_form_requires_auth(): void
    {
        $response = $this->get('/api/conviviente-builder-form/1/0');

        $response->assertRedirect('/login');
    }

    public function test_conviviente_crear_form_requires_auth(): void
    {
        $response = $this->get('/api/conviviente-crear-form/1');

        $response->assertRedirect('/login');
    }

    public function test_conviviente_crear_requires_auth(): void
    {
        $response = $this->post('/api/conviviente-crear');

        $response->assertRedirect('/login');
    }

    public function test_mark_wizard_completed_returns_404_for_nonexistent(): void
    {
        $response = $this->postJson('/api/onboarders/wizards/99999/mark-completed');

        $response->assertStatus(404);
    }
}
