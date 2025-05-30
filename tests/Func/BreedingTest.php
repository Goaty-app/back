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
        $responseData = $this->postRequest('breeding');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('breeding');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetInCollection(int $createdId): void
    {
        $responseData = $this->getRequest('animal/1/breeding');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
        $this->assertSame($createdId, $this->filterCreated($responseData, $createdId)['id']);
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("breeding/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("breeding/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("breeding/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("breeding/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("breeding/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
