<?php

namespace App\Tests\Func;

use App\Enum\Operation;
use App\Tests\Trait\PaylodableTrait;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class FoodStockHistoryTest extends AbstractApiTestCase
{
    use PaylodableTrait;

    private static ?int $foodStockId = null;

    private static function setRequiredPayload(): array
    {
        return [
            'quantity'  => (float) 500,
            'operation' => Operation::PLUS->value,
        ];
    }

    protected function overrideSetUp(): void
    {
        static::setupPayload();

        if (!static::$foodStockId) {
            $herdId = $this->create('herd', HerdTest::getRequiredPayload());
            $foodStockTypeId = $this->create('food-stock-type', FoodStockTypeTest::getRequiredPayload());
            static::$foodStockId = $this->create("herd/{$herdId}/food-stock", array_merge(FoodStockTest::getRequiredPayload(), ['foodStockTypeId' => $foodStockTypeId]));
            static::$implicitPayload['foodStockId'] = static::$foodStockId;
        }
    }

    public function testCreate(): int
    {
        $responseData = $this->postRequest('food-stock/'.static::$foodStockId.'/food-stock-history');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('food-stock/'.static::$foodStockId.'/food-stock-history');

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
