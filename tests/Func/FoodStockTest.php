<?php

namespace App\Tests\Func;

use App\Enum\QuantityUnit;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class FoodStockTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'name'            => 'Silo 1',
        'quantityUnit'    => QuantityUnit::KILOGRAM->value,
        'foodStockTypeId' => 1,
    ];

    public static $optionalPayload = [
        'name' => 'Silo 2',
    ];

    protected static $implicitPayload = [
        'herdId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herd/1/food-stock');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('food-stock', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('food-stock');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herd/1/food-stock');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCollection($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("food-stock/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("food-stock/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("food-stock/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('food-stock', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("food-stock/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("food-stock/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('food-stock', $createdId);
    }
}
