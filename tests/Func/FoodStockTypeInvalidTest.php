<?php

namespace App\Tests\Func;

use App\Entity\FoodStockType;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class FoodStockTypeInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('food-stock-type', $payload, Response::HTTP_UNPROCESSABLE_ENTITY);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            FoodStockType::class,
            array_merge(FoodStockTypeTest::$requiredPayload, FoodStockTypeTest::$optionalPayload),
        );
    }
}
