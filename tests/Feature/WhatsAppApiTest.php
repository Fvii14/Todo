<?php

namespace Tests\Feature;

use App\Models\Answer;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WhatsAppApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_identify_user_requires_phone_and_email(): void
    {
        $response = $this->postJson('/api/whatsapp/identify-user', []);

        $response->assertStatus(422);
    }

    public function test_identify_user_requires_valid_email(): void
    {
        $response = $this->postJson('/api/whatsapp/identify-user', [
            'phone' => '+34600000000',
            'email' => 'not-an-email',
        ]);

        $response->assertStatus(422);
    }

    public function test_identify_user_returns_404_for_unknown_email(): void
    {
        $response = $this->postJson('/api/whatsapp/identify-user', [
            'phone' => '+34600000000',
            'email' => 'nonexistent@example.com',
        ]);

        $response->assertStatus(404)
            ->assertJson(['success' => false]);
    }

    public function test_identify_user_returns_404_when_phone_does_not_match(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $question = Question::create([
            'slug' => 'telefono',
            'text' => 'Teléfono',
            'type' => 'string',
        ]);

        Answer::create([
            'answer' => '+34600000000',
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);

        $response = $this->postJson('/api/whatsapp/identify-user', [
            'phone' => '+34699999999',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'El teléfono no coincide con el correo electrónico proporcionado',
            ]);
    }

    public function test_identify_user_succeeds_with_matching_phone_and_email(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $question = Question::create([
            'slug' => 'telefono',
            'text' => 'Teléfono',
            'type' => 'string',
        ]);

        Answer::create([
            'answer' => '+34600000000',
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);

        $response = $this->postJson('/api/whatsapp/identify-user', [
            'phone' => '+34600000000',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => 'test@example.com',
                ],
            ])
            ->assertJsonStructure([
                'success',
                'user' => ['id', 'name', 'email'],
                'contrataciones',
            ]);
    }

    public function test_identify_user_normalizes_phone_numbers(): void
    {
        $user = User::factory()->create(['email' => 'test@example.com']);

        $question = Question::create([
            'slug' => 'telefono',
            'text' => 'Teléfono',
            'type' => 'string',
        ]);

        Answer::create([
            'answer' => '+34 600 000 000',
            'user_id' => $user->id,
            'question_id' => $question->id,
        ]);

        $response = $this->postJson('/api/whatsapp/identify-user', [
            'phone' => '+34600000000',
            'email' => 'test@example.com',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);
    }

    public function test_upload_document_rejects_missing_fields(): void
    {
        $response = $this->postJson('/api/whatsapp/upload-document', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Datos requeridos faltantes',
            ]);
    }

    public function test_upload_document_rejects_non_numeric_contract_id(): void
    {
        $response = $this->postJson('/api/whatsapp/upload-document', [
            'contract_id' => 'abc',
            'document_slug' => 'dni',
            'files' => [['name' => 'test.pdf', 'content' => 'base64data']],
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'contract_id debe ser un número',
            ]);
    }
}
