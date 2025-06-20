<?php

namespace App\Tests\Func;

use App\Entity\AnimalType;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class AnimalTypeInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('animal-types', $payload, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            AnimalType::class,
            array_merge(AnimalTypeTest::$requiredPayload, AnimalTypeTest::$optionalPayload),
        );
    }
}
