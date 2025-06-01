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
        'notes'           => 'Production de lait',
    ];

    protected static $implicitPayload = [
        'herdId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herd/1/production');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('production', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('production');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herd/1/production');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCollection($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("production/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("production/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("production/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('production', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("production/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("production/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('production', $createdId);
    }
}
