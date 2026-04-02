<?php

namespace Tests\Unit;

use App\Services\BrevoService;
use Brevo\Client\Api\ContactsApi;
use Brevo\Client\Model\CreateContact;
use Brevo\Client\Model\CreateUpdateContactModel;
use Mockery;
use Tests\TestCase;

class BrevoServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    private function makeService(?ContactsApi $contactsApi = null): BrevoService
    {
        $service = new BrevoService;

        if ($contactsApi) {
            $reflection = new \ReflectionClass($service);
            $property = $reflection->getProperty('contactsApi');
            $property->setAccessible(true);
            $property->setValue($service, $contactsApi);
        }

        return $service;
    }

    public function test_create_contact_returns_id(): void
    {
        $contactsApi = Mockery::mock(ContactsApi::class);

        $responseModel = Mockery::mock(CreateUpdateContactModel::class);
        $responseModel->shouldReceive('getId')->andReturn(42);

        $contactsApi->shouldReceive('createContact')
            ->once()
            ->andReturn($responseModel);

        $service = $this->makeService($contactsApi);

        $result = $service->createContact([
            'email' => 'test@example.com',
            'nombre' => 'Test User',
        ]);

        $this->assertEquals(42, $result);
    }

    public function test_update_contact_by_email(): void
    {
        $contactsApi = Mockery::mock(ContactsApi::class);
        $contactsApi->shouldReceive('updateContact')
            ->once()
            ->withArgs(function ($body, $contactId, $identifierType) {
                return $contactId === 'test@example.com'
                    && $identifierType === 'email_id';
            });

        $service = $this->makeService($contactsApi);

        $service->updateContact([
            'email' => 'test@example.com',
            'TELEFONO' => '612345678',
        ]);

        // No exception = pass
        $this->assertTrue(true);
    }

    public function test_update_contact_without_identifier_does_not_throw(): void
    {
        $contactsApi = Mockery::mock(ContactsApi::class);
        $contactsApi->shouldNotReceive('updateContact');

        $service = $this->makeService($contactsApi);

        // Should log error but not throw
        $service->updateContact([
            'TELEFONO' => '612345678',
        ]);

        $this->assertTrue(true);
    }

    public function test_delete_contact(): void
    {
        $contactsApi = Mockery::mock(ContactsApi::class);
        $contactsApi->shouldReceive('deleteContact')
            ->once()
            ->with(42);

        $service = $this->makeService($contactsApi);

        $service->deleteContact(42);

        $this->assertTrue(true);
    }

    public function test_add_emails_to_list(): void
    {
        $contactsApi = Mockery::mock(ContactsApi::class);
        $contactsApi->shouldReceive('addContactToList')
            ->once()
            ->withArgs(function ($body, $listId) {
                return $listId === 174;
            });

        $service = $this->makeService($contactsApi);

        $service->addEmailsToList(174, ['test@example.com']);

        $this->assertTrue(true);
    }
}
