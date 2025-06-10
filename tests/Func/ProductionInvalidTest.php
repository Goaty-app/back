<?php

namespace App\Tests\Func;

use App\Entity\Production;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class ProductionInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('herds/1/productions', $payload, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            Production::class,
            array_merge(ProductionTypeTest::$requiredPayload, ProductionTypeTest::$optionalPayload),
        );
    }
}
