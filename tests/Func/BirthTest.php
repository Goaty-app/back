<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class BirthTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'childId' => 1,
    ];

    public static $optionalPayload = [
        'birthDate'   => '2025-01-01 00:00:00',
        'birthWeight' => 2500.0,
        'notes'       => 'Great',
        'breedingId'  => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('births');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('births', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('births');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("births/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("births/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("births/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('births', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("births/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("births/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('births', $createdId);
    }
}
