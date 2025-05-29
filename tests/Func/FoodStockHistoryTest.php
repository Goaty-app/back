<?php

namespace App\Tests\Func;

use App\Enum\Operation;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class FoodStockHistoryTest extends AbstractApiTestCase
{
    protected static $requiredPayload = [
        'quantity'  => 500.0,
        'operation' => Operation::PLUS->value,
    ];

    protected static $implicitPayload = [
        'foodStockId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('food-stock/1/food-stock-history');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('food-stock/1/food-stock-history');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("food-stock-history/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("food-stock-history/{$createdId}");

        $this->getRequest("food-stock-history/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
