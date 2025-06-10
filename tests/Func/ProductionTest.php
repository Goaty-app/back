<?php

namespace App\Tests\Func;

use App\Enum\QuantityUnit;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class ProductionTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'production_date'  => '2025-01-01 00:00:00',
        'quantity'         => 1500.0,
        'quantityUnit'     => QuantityUnit::OUNCE->value,
        'productionTypeId' => 1,
    ];

    public static $optionalPayload = [
        'expiration_date' => '2025-02-01 00:00:00',
        'notes'           => 'productions de lait',
    ];

    protected static $implicitPayload = [
        'herdId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herds/1/productions');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('productions', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('productions');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herds/1/productions');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCollection($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("productions/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("productions/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("productions/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('productions', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("productions/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("productions/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('productions', $createdId);
    }
}
