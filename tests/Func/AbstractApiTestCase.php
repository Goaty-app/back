<?php

namespace App\Tests\Func;

use App\Tests\Helper\TestLoginHelper; // Ou la méthode que tu utilises pour te connecter
use DateTime;
use DateTimeZone;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AbstractApiTestCase extends WebTestCase
{
    private const API_ENDPOINT_BASE = '/api/v1';

    private KernelBrowser $client;

    protected static $implicitPayload = [];
    protected static $requiredPayload = [];
    protected static $optionalPayload = [];

    final protected function setUp(): void
    {
        parent::setUp();
        $this->client = static::createClient();
        $this->client = TestLoginHelper::login($this->client);
        $this->overrideSetUp();
    }

    protected function overrideSetUp(): void
    {
    }

    public static function tearDownAfterClass(): void
    {
        static::$implicitPayload = [];
        static::$requiredPayload = [];
        static::$optionalPayload = [];
    }

    final protected function getRequest(string $uri, array $parameters = [], int $expectedStatusCode = Response::HTTP_OK): ?array
    {
        $this->client->request('GET', $this->generateFullUrl($uri), $parameters);
        $this->assertResponseStatusCodeSame($expectedStatusCode, $this->client->getResponse()->getContent());

        if (Response::HTTP_OK !== $expectedStatusCode) {
            return null;
        }

        $this->assertResponseHeaderSame('content-type', 'application/json', $this->client->getResponse()->getContent());

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    final protected function postRequest(string $uri, ?array $payload = null, int $expectedStatusCode = Response::HTTP_CREATED): ?array
    {
        $this->client->request(
            'POST',
            $this->generateFullUrl($uri),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode($payload ?? static::$requiredPayload),
        );
        $this->assertResponseStatusCodeSame($expectedStatusCode, $this->client->getResponse()->getContent());
        $this->assertResponseHeaderSame('content-type', 'application/json', $this->client->getResponse()->getContent());

        return json_decode($this->client->getResponse()->getContent(), true);
    }

    final protected function patchRequest(string $uri, ?array $payload = null, int $expectedStatusCode = Response::HTTP_NO_CONTENT): void
    {
        $this->client->request(
            'PATCH',
            $this->generateFullUrl($uri),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode($payload ?? static::$optionalPayload),
        );
        $this->assertResponseStatusCodeSame($expectedStatusCode, $this->client->getResponse()->getContent());
    }

    final protected function deleteRequest(string $uri, int $expectedStatusCode = Response::HTTP_NO_CONTENT): void
    {
        $this->client->request('DELETE', $this->generateFullUrl($uri));
        $this->assertResponseStatusCodeSame($expectedStatusCode, $this->client->getResponse()->getContent());
    }

    final protected function assertCreatedModel(array $data): void
    {
        foreach (array_merge(static::$implicitPayload, static::$requiredPayload) as $key => $value) {
            $value = $this->convertDateIfNeeded($value);
            if ($this->isRelateKey($key)) {
                $this->assertSame($value, $data[$this->getRelateKeyValue($key)]['id']);
            } else {
                $this->assertSame($value, $data[$key]);
            }
        }
    }

    final protected function assertUpdateModel(array $data): void
    {
        foreach (array_merge(static::$implicitPayload, static::$requiredPayload, static::$optionalPayload) as $key => $value) {
            $value = $this->convertDateIfNeeded($value);
            if ($this->isRelateKey($key)) {
                $this->assertSame($value, $data[$this->getRelateKeyValue($key)]['id']);
            } else {
                $this->assertSame($value, $data[$key]);
            }
        }
    }

    final protected function assertModel(mixed $data): void
    {
        $this->assertArrayHasKey('id', $data);
        $this->assertIsInt($data['id']);

        foreach (array_merge(static::$requiredPayload, static::$implicitPayload) as $key => $value) {
            if ($this->isRelateKey($key)) {
                $this->assertArrayHasKey($this->getRelateKeyValue($key), $data);
                $this->assertTrue(
                    \gettype($value) === \gettype($data[$this->getRelateKeyValue($key)]['id']),
                    \sprintf(
                        '%s (%s) %s (%s)',
                        $value,
                        \gettype($value),
                        $data[$this->getRelateKeyValue($key)]['id'],
                        \gettype($data[$this->getRelateKeyValue($key)]['id']),
                    ),
                );
            } else {
                $this->assertArrayHasKey($key, $data);
                $this->assertTrue(
                    \gettype($value) === \gettype($data[$key]),
                    \sprintf(
                        '%s (%s) %s (%s)',
                        $value,
                        \gettype($value),
                        $data[$key],
                        \gettype($data[$key]),
                    ),
                );
            }
        }

        foreach (static::$optionalPayload as $key => $value) {
            if ($this->isRelateKey($key)) {
                $this->assertArrayHasKey($this->getRelateKeyValue($key), $data);
                $this->assertTrue(
                    null === $data[$this->getRelateKeyValue($key)]
                    || \gettype($value) === \gettype($data[$this->getRelateKeyValue($key)]['id']),
                    \sprintf(
                        '%s (%s) %s (%s)',
                        $value,
                        \gettype($value),
                        $data[$this->getRelateKeyValue($key)]['id'],
                        \gettype($data[$this->getRelateKeyValue($key)]['id']),
                    ),
                );
            } else {
                $this->assertArrayHasKey($key, $data);
                $this->assertTrue(
                    null === $data[$key]
                    || \gettype($value) === \gettype($data[$key]),
                    \sprintf(
                        '%s (%s) %s (%s)',
                        $value,
                        \gettype($value),
                        $data[$key],
                        \gettype($data[$key]),
                    ),
                );
            }
        }
    }

    final protected function filterCreated(array $data, int $id): array
    {
        return array_find($data, fn ($row) => $row['id'] === $id);
    }

    private function convertDateIfNeeded(mixed $value): mixed
    {
        if (!\is_string($value)) {
            return $value;
        }

        $dateTime = DateTime::createFromFormat('Y-m-d H:i:s', $value);

        if (false !== $dateTime) {
            $dateTime->setTimezone(new DateTimeZone('UTC'));

            return $dateTime->format('Y-m-d\TH:i:s+00:00');
        }

        return $value;
    }

    private function generateFullUrl(string $uri)
    {
        return self::API_ENDPOINT_BASE.'/'.$uri;
    }

    private function isRelateKey(string $key): bool
    {
        return str_ends_with($key, 'Id');
    }

    private function getRelateKeyValue(string $key): string
    {
        return substr($key, 0, -2);
    }
}
