<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class ProductionTypeTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'name' => 'Laitier',
    ];

    public static $optionalPayload = [
        'name' => 'Viande',
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('production-type');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('production-type', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('production-type');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("production-type/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("production-type/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("production-type/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('production-type', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("production-type/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("production-type/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('production-type', $createdId);
    }
}
