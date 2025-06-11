<?php

namespace App\Tests\Func;

class MeTest extends AbstractApiTestCase
{
    public static $requiredPayload = [
        'email' => 'admin@example.com',
    ];

    public function testGetMe(): void
    {
        $responseData = $this->getRequest('me');

        $this->assertModelTypes($responseData);
        $this->assertCreatedModel($responseData);
    }
}
