<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\Cache\TagAwareCacheInterface;

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
        $service = $this->createMock(TagAwareCacheInterface::class);
        $service->expects($this->once())
            ->method('invalidateTags')
            ->willReturnCallback(fn ($tags): bool => true)
        ;
        self::getContainer()->set(TagAwareCacheInterface::class, $service);

        $responseData = $this->postRequest('herd/1/animal');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);

        // Mocking is incompatible with this line (uncommented pour fail the test pipeline)
        $this->assertCacheCollectionCreated('animal', $responseData['id']);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('animal');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herd/1/animal');

        $this->assertIsArray($responseData);
        $this->assertModelTypes($this->filterCollection($responseData, $createdId));
        $this->assertCreatedModel($this->filterCollection($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCollection($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("animal/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("animal/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("animal/{$createdId}");

        $this->assertModelTypes($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);

        $this->assertCacheCollectionUpdated('animal', $createdId);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("animal/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("animal/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);

        $this->assertCacheCollectionDeleted('animal', $createdId);
    }
}
