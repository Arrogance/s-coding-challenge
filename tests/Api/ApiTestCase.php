<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiTestCase extends WebTestCase
{
    protected static $staticClient;
    protected $client;
    protected $token;

    protected function setUp(): void
    {
        parent::setUp();
        $this->client = self::$staticClient;
    }

    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();
        self::$staticClient = static::createClient();

        self::bootKernel();

        $entityManager = static::$kernel->getContainer()->get('doctrine.orm.entity_manager');
        $metadata = $entityManager->getMetadataFactory()->getAllMetadata();

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    protected function getResponseData(Response $response): array
    {
        return json_decode($response->getContent(), true);
    }

    protected function login(string $email, string $password): void
    {
        $this->client->request(
            'POST',
            '/login',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode(['email' => $email, 'password' => $password])
        );

        $response = $this->client->getResponse();
        $this->assertEquals(200, $response->getStatusCode());

        $data = $this->getResponseData($response);
        $this->token = $data['token'];
    }

    protected function jsonRequest(string $method, string $uri, array $data = [], array $headers = []): Response
    {
        $defaultHeaders = ['CONTENT_TYPE' => 'application/json'];
        if ($this->token) {
            $defaultHeaders['HTTP_AUTHORIZATION'] = 'Bearer '.$this->token;
        }

        $this->client->request(
            $method,
            $uri,
            [],
            [],
            array_merge($defaultHeaders, $headers),
            $data ? json_encode($data) : null
        );

        return $this->client->getResponse();
    }

    protected function debug(Response $response): void
    {
        echo "\n--- DEBUG RESPONSE ---\n";
        echo 'Status: '.$response->getStatusCode()."\n";
        echo 'Headers: '.json_encode($response->headers->all(), \JSON_PRETTY_PRINT)."\n";
        echo 'Body: '.json_encode($this->getResponseData($response), \JSON_PRETTY_PRINT)."\n";
        echo "--- END DEBUG ---\n";
    }

    protected function setAuthorizationHeader(string $token): void
    {
        $this->client->setServerParameter('HTTP_Authorization', 'Bearer '.$token);
    }
}
