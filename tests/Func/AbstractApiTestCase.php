<?php

// tests/Functional/AbstractApiTestCase.php ou tests/Helper/AbstractApiTestCase.php

namespace App\Tests\Func;

use App\Tests\Helper\TestLoginHelper; // Ou la méthode que tu utilises pour te connecter
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AbstractApiTestCase extends WebTestCase
{
    private const API_ENDPOINT_BASE = '/api/v1';

    private KernelBrowser $client;

    public static function getImplicitPayload(): array
    {
        return [];
    }

    public static function getRequiredPayload(): array
    {
        return [];
    }

    public static function getOptionalPayload(): array
    {
        return [];
    }

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
            json_encode($payload ?? static::getRequiredPayload()),
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
            json_encode($payload ?? static::getOptionalPayload()),
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
        foreach (array_merge(static::getImplicitPayload(), static::getRequiredPayload()) as $key => $value) {
            if ($this->isRelateKey($key)) {
                $this->assertSame($value, $data[$this->getRelateKeyValue($key)]['id']);
            } else {
                $this->assertSame($value, $data[$key]);
            }
        }
    }

    final protected function assertUpdateModel(array $data): void
    {
        foreach (array_merge(static::getImplicitPayload(), static::getRequiredPayload(), static::getOptionalPayload()) as $key => $value) {
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

        foreach (array_merge(static::getRequiredPayload(), static::getImplicitPayload()) as $key => $value) {
            if ($this->isRelateKey($key)) {
                $this->assertArrayHasKey($this->getRelateKeyValue($key), $data);
                $this->assertTrue(\gettype($value) === \gettype($data[$this->getRelateKeyValue($key)]['id']));
            } else {
                $this->assertArrayHasKey($key, $data);
                $this->assertTrue(
                    \gettype($value) === \gettype($data[$key]),
                        // todo : remove this line
                    "{$value} => {$data[$key]}".\gettype($value).' : '.\gettype($data[$key]),
                );
            }
        }

        foreach (static::getOptionalPayload() as $key => $value) {
            if ($this->isRelateKey($key)) {
                $this->assertArrayHasKey($this->getRelateKeyValue($key), $data);
                $this->assertTrue(
                    null === $data[$this->getRelateKeyValue($key)]
                    || \gettype($value) === \gettype($data[$this->getRelateKeyValue($key)]['id']),
                );
            } else {
                $this->assertArrayHasKey($key, $data);
                $this->assertTrue(
                    null === $data[$key]
                    || \gettype($value) === \gettype($data[$key]),
                );
            }
        }
    }

    final protected function filterCreated(array $data, int $id): array
    {
        return array_find($data, fn ($row) => $row['id'] === $id);
    }

    final public function create(string $uri, array $payload): int
    {
        $this->client->request(
            'POST',
            $this->generateFullUrl($uri),
            [],
            [],
            ['CONTENT_TYPE' => 'application/json', 'HTTP_ACCEPT' => 'application/json'],
            json_encode($payload),
        );

        return json_decode($this->client->getResponse()->getContent(), true)['id'];
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
