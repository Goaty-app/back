<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class HealthcareTypeTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'name' => 'Stérilisation',
    ];

    public static $optionalPayload = [
        'name' => 'Traitements antiparasitaires',
    ];

    public function testCreate(): int
    {
        $responseData = $this->postRequest('healthcare-type');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('healthcare-type');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("healthcare-type/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("healthcare-type/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("healthcare-type/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("healthcare-type/{$createdId}");

        // Verify if the data is deleted
        $this->getRequest("healthcare-type/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
