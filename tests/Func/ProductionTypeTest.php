<?php

namespace App\Tests\Func;

use App\Tests\Trait\PaylodableTrait;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class ProductionTypeTest extends AbstractApiTestCase
{
    use PaylodableTrait;

    private static function setRequiredPayload(): array
    {
        return [
            'name' => 'Lait',
        ];
    }

    private static function setOptionalPayload(): array
    {
        return [
            'name' => 'Lactosérum',
        ];
    }

    public function testCreate(): int
    {
        $responseData = $this->postRequest('production-type');

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);

        return $responseData['id'];
    }

    #[Depends('testCreate')]
    public function testGetCollection(int $createdId): void
    {
        $responseData = $this->getRequest('production-type');

        $this->assertIsArray($responseData);
        $this->assertModel($this->filterCreated($responseData, $createdId));
        $this->assertCreatedModel($this->filterCreated($responseData, $createdId));
    }

    #[Depends('testCreate')]
    public function testGetById(int $createdId): void
    {
        $responseData = $this->getRequest("production-type/{$createdId}");

        $this->assertModel($responseData);
        $this->assertCreatedModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testUpdate(int $createdId): void
    {
        $this->patchRequest("production-type/{$createdId}");

        // Verify if the data is updated
        $responseData = $this->getRequest("production-type/{$createdId}");

        $this->assertModel($responseData);
        $this->assertUpdateModel($responseData);
        $this->assertSame($createdId, $responseData['id']);
    }

    #[Depends('testCreate')]
    public function testDelete(int $createdId): void
    {
        $this->deleteRequest("production-type/{$createdId}");

        $this->getRequest("production-type/{$createdId}", expectedStatusCode: Response::HTTP_NOT_FOUND);
    }
}
