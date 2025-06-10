<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class HealthcareTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'healthcareTypeId' => 1,
    ];

    public static $optionalPayload = [
        'description' => 'Vaccination',
    ];

    protected static $implicitPayload = [
        'animalId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('animals/1/healthcares');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('healthcares', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('healthcares');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('animals/1/healthcares');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCollection($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("healthcares/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("healthcares/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("healthcares/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('healthcares', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("healthcares/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("healthcares/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('healthcares', $createdId);
    }
}
