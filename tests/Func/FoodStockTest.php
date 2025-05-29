<?php

namespace App\Tests\Func;

use App\Enum\QuantityUnit;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class FoodStockTest extends AbstractApiTestCase
{
    protected static $requiredPayload = [
        'name'            => 'Silo 1',
        'quantityUnit'    => QuantityUnit::KILOGRAM->value,
        'foodStockTypeId' => 1,
    ];

    protected static $optionalPayload = [
        'name' => 'Silo 2',
    ];

    protected static $implicitPayload = [
        'herdId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herd/1/food-stock');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('food-stock');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herd/1/food-stock');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCreated($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("food-stock/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("food-stock/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("food-stock/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("food-stock/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("food-stock/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
