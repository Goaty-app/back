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
        $responseData = $this->postRequest('herds/1/food-stocks');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('food-stocks', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('food-stocks');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herds/1/food-stocks');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCollection($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("food-stocks/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("food-stocks/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("food-stocks/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('food-stocks', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("food-stocks/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("food-stocks/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('food-stocks', $createdId);
    }
}
