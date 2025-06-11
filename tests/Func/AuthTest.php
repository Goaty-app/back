<?php

namespace App\Tests\Func;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class AuthTest extends WebTestCase
{
    public function testLoginCheckSuccessful(): void
    {
        $client = static::createClient();

        $credentials = [
            'username' => 'admin@example.com',
            'password' => 'password',
        ];

        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($credentials),
        );

        $this->assertResponseIsSuccessful();
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('token', $responseData);
        $this->assertNotEmpty($responseData['token']);

        $this->assertArrayHasKey('refresh_token', $responseData);
        $this->assertNotEmpty($responseData['refresh_token']);
    }

    public function testLoginCheckFailedWithWrongCredentials(): void
    {
        $client = static::createClient();

        $credentials = [
            'username' => 'admin@example.com',
            'password' => 'wrongpassword',
        ];

        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($credentials),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
    }

    public function testRegisterCheckSuccessful(): void
    {
        $client = static::createClient();

        $credentials = [
            'email'    => 'alberto@example.com',
            'password' => 'JeSuisUnSuperMot980DePasse/',
        ];

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($credentials),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_CREATED);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);

        $this->assertArrayHasKey('email', $responseData);
        $this->assertNotEmpty($responseData['email']);
    }

    public function testRegisterFailedWithExistingEmail(): void
    {
        $client = static::createClient();

        $credentials = [
            'email'    => 'admin@example.com',
            'password' => 'JeSuisUnSuperMot980DePasse/',
        ];

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($credentials),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('violations', $responseData);
    }

    public function testRegisterFailedWithWeakPassword(): void
    {
        $client = static::createClient();

        $credentials = [
            'email'    => 'admin@example.com',
            'password' => 'password',
        ];

        $client->request(
            'POST',
            '/api/register',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($credentials),
        );

        $this->assertResponseStatusCodeSame(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->assertResponseHeaderSame('content-type', 'application/json');

        $responseData = json_decode($client->getResponse()->getContent(), true);
        $this->assertArrayHasKey('message', $responseData);
        $this->assertArrayHasKey('violations', $responseData);
    }
}
