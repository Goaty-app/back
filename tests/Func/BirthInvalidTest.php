<?php

namespace App\Tests\Func;

use App\Entity\Birth;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class BirthInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('birth', $payload, Response::HTTP_BAD_REQUEST);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            Birth::class,
            array_merge(BirthTest::$requiredPayload, BirthTest::$optionalPayload),
        );
    }
}
