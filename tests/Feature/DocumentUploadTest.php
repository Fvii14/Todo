<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentUploadTest extends TestCase
{
    use RefreshDatabase;

    public function test_document_store_requires_authentication(): void
    {
        $response = $this->post('/documento');
        $response->assertRedirect('/login');
    }

    public function test_document_destroy_requires_authentication(): void
    {
        $response = $this->delete('/documento/1');
        $response->assertRedirect('/login');
    }

    public function test_user_document_update_requires_authentication(): void
    {
        $response = $this->patch('/user-documents/1', ['estado' => 'aprobado']);
        $response->assertRedirect('/login');
    }

    public function test_user_document_destroy_requires_authentication(): void
    {
        $response = $this->delete('/user-documents/1');
        $response->assertRedirect('/login');
    }
}
