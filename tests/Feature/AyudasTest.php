<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AyudasTest extends TestCase
{
    use RefreshDatabase;

    public function test_ayudas_index_requires_authentication(): void
    {
        $response = $this->get('/dashboard/ayudas');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_ayudas_index(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard/ayudas');

        $response->assertSuccessful();
    }

    public function test_ayudas_delete_requires_authentication(): void
    {
        $response = $this->delete('/ayudas/1');
        $response->assertRedirect('/login');
    }

    public function test_ayudas_solicitadas_requires_authentication(): void
    {
        $response = $this->get('/ayudas-solicitadas');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_view_ayudas_solicitadas(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/ayudas-solicitadas');

        $response->assertSuccessful();
    }
}
