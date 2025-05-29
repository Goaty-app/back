<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class HerdTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'name' => 'Troupeau des Écrins',
    ];

    public static $optionalPayload = [
        'location' => 'Hautes-Alpes',
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('herd');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('herd');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("herd/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("herd/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("herd/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("herd/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("herd/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
