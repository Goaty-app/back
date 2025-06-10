<?php

namespace App\Tests\Func;

use App\Entity\Herd;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class HerdInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('herds', $payload, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            Herd::class,
            array_merge(HerdTest::$requiredPayload, HerdTest::$optionalPayload),
        );
    }
}
