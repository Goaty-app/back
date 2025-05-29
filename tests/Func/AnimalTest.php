<?php

namespace App\Tests\Func;

use App\Tests\Trait\PaylodableTrait;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class AnimalTest extends AbstractApiTestCase
{
    use PaylodableTrait;

    private static ?int $herdId = null;
    private static ?int $animalTypeId = null;

    private static function setRequiredPayload(): array
    {
        return [
            'idNumber'     => 'GOAT-001',
            'status'       => 'Adult',
            'animalTypeId' => null,
        ];
    }

    private static function setOptionalPayload(): array
    {
        return [
            'name'          => 'Napoléon Bonabroute',
            'behaviorNotes' => 'Ne broute plus',
            'originCountry' => 'FR',
            'gender'        => 'male',
        ];
    }

    protected function overrideSetUp(): void
    {
        static::setupPayload();

        if (!static::$herdId) {
            static::$herdId = $this->create('herd', HerdTest::getRequiredPayload());
            static::$implicitPayload['herdId'] = static::$herdId;
        }

        if (!static::$animalTypeId) {
            static::$animalTypeId = $this->create('animal-type', AnimalTypeTest::getRequiredPayload());
            static::$requiredPayload['animalTypeId'] = static::$animalTypeId;
        }
    }

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herd/'.static::$herdId.'/animal');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('animal');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herd/'.static::$herdId.'/animal');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCreated($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("animal/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("animal/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("animal/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("animal/{$createdId}");

        $this->getRequest("animal/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
