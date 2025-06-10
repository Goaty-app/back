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
        $responseData = $this->postRequest('production-types');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('production-types', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('production-types');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("production-types/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("production-types/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("production-types/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('production-types', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("production-types/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("production-types/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('production-types', $createdId);
    }
}
