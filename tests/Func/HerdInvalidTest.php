<?php

namespace App\Tests\Func;

use PHPUnit\Framework\Attributes\DataProvider;
use Symfony\Component\HttpFoundation\Response;

class HerdInvalidTest extends AbstractApiTestCase
{
    #[DataProvider('provideInvalidHerdDataForCreation')]
    public function testCreateHerdWithInvalidData(array $payload): void
    {
        $this->postRequest('herd', $payload, Response::HTTP_BAD_REQUEST);
    }

    public static function provideInvalidHerdDataForCreation(): iterable
    {
        yield 'missing (name)' => [
            'payload' => ['location' => 'Hautes-Alpes'],
        ];
        yield 'empty (name)' => [
            'payload' => ['name' => '', 'location' => 'Hautes-Alpes'],
        ];
        yield 'missing (name) and (location)' => [
            'payload' => [],
        ];
        yield 'too long (name)' => [
            'payload' => ['name' => str_repeat('a', 256), 'location' => 'Hautes-Alpes'],
        ];
        yield 'invalid (name)' => [
            'payload' => ['name' => 123, 'location' => 'Hautes-Alpes'],
        ];
        yield 'invalid (location)' => [
            'payload' => ['name' => 'Troupeau des Écrins', 'location' => 123],
        ];
    }
}
