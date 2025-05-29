<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class HealthcareTest extends AbstractApiTestCase
{
    protected static $requiredPayload = [
        'healthcareTypeId' => 1,
    ];

    protected static $optionalPayload = [
        'description' => 'Vaccination',
    ];

    protected static $implicitPayload = [
        'animalId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('animal/1/healthcare');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('healthcare');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('animal/1/healthcare');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCreated($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("healthcare/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("healthcare/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("healthcare/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("healthcare/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("healthcare/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
