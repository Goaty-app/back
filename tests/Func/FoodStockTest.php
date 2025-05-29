<?php

namespace App\Tests\Func;

use App\Enum\QuantityUnit;
use App\Tests\Trait\PaylodableTrait;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class FoodStockTest extends AbstractApiTestCase
{
    use PaylodableTrait;

    private static ?int $herdId = null;
    private static ?int $foodStockTypeId = null;

    private static function setRequiredPayload(): array
    {
        return [
            'name'            => 'Grenier 1',
            'quantity'        => (float) 0,
            'quantityUnit'    => QuantityUnit::OUNCE->value,
            'foodStockTypeId' => null,
        ];
    }

    private static function setOptionalPayload(): array
    {
        return [
            'name' => 'Grenier 2',
        ];
    }

    protected function overrideSetUp(): void
    {
        static::setupPayload();

        if (!static::$herdId) {
            static::$herdId = $this->create('herd', HerdTest::getRequiredPayload());
            static::$implicitPayload['herdId'] = static::$herdId;
        }

        if (!static::$foodStockTypeId) {
            static::$foodStockTypeId = $this->create('food-stock-type', FoodStockTypeTest::getRequiredPayload());
            static::$requiredPayload['foodStockTypeId'] = static::$foodStockTypeId;
        }
    }

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herd/'.static::$herdId.'/food-stock');

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
        $responseData = $this->getRequest('herd/'.static::$herdId.'/food-stock');

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

        $this->getRequest("food-stock/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
