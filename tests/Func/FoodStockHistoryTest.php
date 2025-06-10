<?php

namespace App\Tests\Func;

use App\Enum\Operation;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class FoodStockHistoryTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'quantity'  => 500.0,
        'operation' => Operation::PLUS->value,
    ];

    protected static $implicitPayload = [
        'foodStockId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('food-stocks/1/food-stock-histories');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('food-stocks/1/food-stock-histories');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("food-stock-histories/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("food-stock-histories/{$createdId}");

        $this->getRequest("food-stock-histories/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
