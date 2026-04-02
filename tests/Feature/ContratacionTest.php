<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContratacionTest extends TestCase
{
    use RefreshDatabase;

    public function test_contrataciones_list_requires_authentication(): void
    {
        $response = $this->get('/api/contrataciones-usuario/1');
        $response->assertRedirect('/login');
    }

    public function test_admin_tramitacion_requires_authentication(): void
    {
        $response = $this->get('/admin/tramitacion/users/1/contrataciones');
        $response->assertRedirect('/login');
    }

    public function test_contratacion_datos_actualizados_requires_authentication(): void
    {
        $response = $this->get('/contrataciones/1/datos-actualizados');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_fetch_own_contrataciones(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/api/contrataciones-usuario/' . $user->id);

        // Should succeed even if empty
        $response->assertSuccessful();
    }
}
