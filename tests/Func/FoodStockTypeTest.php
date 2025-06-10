<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class FoodStockTypeTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'name' => 'Fouin',
    ];

    public static $optionalPayload = [
        'name' => 'Blé',
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('food-stock-types');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('food-stock-types', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('food-stock-types');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("food-stock-types/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("food-stock-types/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("food-stock-types/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('food-stock-types', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("food-stock-types/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("food-stock-types/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('food-stock-types', $createdId);
    }
}
