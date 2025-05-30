<?php

namespace App\Tests\Func;

use App\Entity\HealthcareType;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class HealthcareTypeInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('healthcare-type', $payload, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            HealthcareType::class,
            array_merge(HealthcareTypeTest::$requiredPayload, HealthcareTypeTest::$optionalPayload),
        );
    }
}
