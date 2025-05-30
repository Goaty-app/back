<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class BirthTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'childId' => 1,
    ];

    public static $optionalPayload = [
        'birthDate'   => '2025-01-01 00:00:00',
        'birthWeight' => 2500.0,
        'notes'       => 'Great',
        'breedingId'  => 1,
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('birth');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('birth');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("birth/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("birth/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("birth/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("birth/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("birth/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
