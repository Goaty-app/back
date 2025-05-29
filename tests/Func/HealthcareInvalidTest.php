<?php

namespace App\Tests\Func;

use App\Entity\Healthcare;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class HealthcareInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('animal/1/healthcare', $payload, Response::HTTP_BAD_REQUEST);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            Healthcare::class,
            array_merge(HealthcareTest::$requiredPayload, HealthcareTest::$optionalPayload),
        );
    }
}
