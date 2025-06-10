<?php

namespace App\Tests\Func;

use App\Entity\ProductionType;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class ProductionTypeInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('production-types', $payload, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            ProductionType::class,
            array_merge(ProductionTypeTest::$requiredPayload, ProductionTypeTest::$optionalPayload),
        );
    }
}
