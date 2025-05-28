<?php

namespace App\Tests\Func;

use App\Enum\QuantityUnit;
use App\Tests\Trait\PaylodableTrait;
use DateTime;
use DateTimeZone;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class ProductionTest extends AbstractApiTestCase
{
    use PaylodableTrait;

    private static ?int $herdId = null;
    private static ?int $productionTypeId = null;

    private static function setRequiredPayload(): array
    {
        return [
            'production_date'  => (new DateTime((new DateTime())->format('Y-m-d'), new DateTimeZone('UTC')))->format(DateTime::ATOM),
            'quantity'         => 69.69,
            'quantityUnit'     => QuantityUnit::KILOGRAM->value,
            'productionTypeId' => null,
        ];
    }

    private static function setOptionalPayload(): array
    {
        return [
            'expiration_date' => (new DateTime((new DateTime())->modify('+120 days')->format('Y-m-d'), new DateTimeZone('UTC')))->format(DateTime::ATOM),
            'notes'           => 'Production de lait',
        ];
    }

    protected function overrideSetUp(): void
    {
        static::setupPayload();

        if (!static::$herdId) {
            static::$herdId = $this->create('herd', HerdTest::getRequiredPayload());
            static::$implicitPayload['herdId'] = static::$herdId;
        }

        if (!static::$productionTypeId) {
            static::$productionTypeId = $this->create('production-type', ProductionTypeTest::getRequiredPayload());
            static::$requiredPayload['productionTypeId'] = static::$productionTypeId;
        }
    }

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herd/'.static::$herdId.'/production');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('production');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herd/'.static::$herdId.'/production');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCreated($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("production/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("production/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("production/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("production/{$createdId}");

        $this->getRequest("production/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
