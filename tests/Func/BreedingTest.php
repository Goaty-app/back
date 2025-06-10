<?php

namespace App\Tests\Func;

use App\Enum\BreedingStatus;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class BreedingTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'femaleId' => 1,
        'maleId'   => 2,
    ];

    public static $optionalPayload = [
        'matingDateStart'    => '2025-01-01 00:00:00',
        'matingDateEnd'      => '2025-02-01 00:00:00',
        'expectedChildCount' => 5,
        'status'             => BreedingStatus::PREGNANT->value,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('breedings');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('breedings', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('breedings');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('animals/1/breedings');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCollection($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("breedings/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("breedings/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("breedings/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('breedings', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("breedings/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("breedings/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('breedings', $createdId);
    }
}
