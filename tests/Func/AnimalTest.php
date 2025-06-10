<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class AnimalTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'idNumber'     => 'GOAT-001',
        'status'       => 'Adult',
        'animalTypeId' => 1,
    ];

    public static $optionalPayload = [
        'name'          => 'Napoléon Bonabroute',
        'behaviorNotes' => 'Ne broute plus',
        'originCountry' => 'FR',
        'gender'        => 'male',
    ];

    protected static $implicitPayload = [
        'herdId' => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herds/1/animals');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        $this->assertCacheCollectionCreated('animals', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('animals');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herds/1/animals');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCollection($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("animals/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("animals/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("animals/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('animals', $createdId);
    }

    #[Depends('testCreate', 'testUpdate')]
    public function testSearch(int $createdId): void
    {
        $name = strtok(static::$optionalPayload['name'], ' ');
        $responseData = $this->getRequest("animals/search?name={$name}");

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("animals/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("animals/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('animals', $createdId);
    }
}
