<?php

namespace App\Tests\Func;

use App\Entity\FoodStock;
use App\Tests\Helper\InvalidPayloadGenerator;
use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class FoodStockInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidDataForCreation')]
    public function testCreateWithInvalidData(array $payload): void
    {
        $this->postRequest('herd/1/food-stock', $payload, Response::HTTP_BAD_REQUEST);
    }

    public static function provideInvalidDataForCreation(): iterable
    {
        $generator = new InvalidPayloadGenerator();

        yield from $generator->generateInvalidData(
            FoodStock::class,
            array_merge(FoodStockTest::$requiredPayload, FoodStockTest::$optionalPayload),
        );
    }
}
