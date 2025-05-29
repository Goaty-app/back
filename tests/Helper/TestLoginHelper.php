<?php

namespace App\Tests\Helper;

use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TestLoginHelper extends WebTestCase
{
    public static function login(
        KernelBrowser $client,
        string $username = 'admin@example.com',
        string $password = 'password',
    ): KernelBrowser {
        $client->request(
            'POST',
            '/api/login_check',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['username' => $username, 'password' => $password]),
        );

        $client->setServerParameter(
            'HTTP_Authorization',
            \sprintf(
                'Bearer %s',
                json_decode($client->getResponse()->getContent(), true)['token'],
            ),
        );

        return $client;
    }
}
