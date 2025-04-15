<?php

declare(strict_types=1);

namespace App\Tests\Api;

use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\Attributes\Depends;
use Symfony\Component\HttpFoundation\Response;

class UserApiTest extends ApiTestCase
{
    protected Generator $faker;
    private array $userData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();

        $this->userData = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(10),
        ];
    }

    public function testCreateUser(): array
    {
        $response = $this->jsonRequest('POST', '/users', $this->userData);
        $this->assertEquals(Response::HTTP_CREATED, $response->getStatusCode());

        $data = $this->getResponseData($response);

        $this->assertArrayHasKey('id', $data);
        $this->assertEquals($this->userData['name'], $data['name']);
        $this->assertEquals($this->userData['email'], $data['email']);

        return [
            'userId' => $data['id'],
            'email' => $this->userData['email'],
            'password' => $this->userData['password'],
        ];
    }

    #[Depends('testCreateUser')]
    public function testAuthenticate(array $user): array
    {
        $response = $this->jsonRequest('POST', '/login', [
            'email' => $user['email'],
            'password' => $user['password'],
        ]);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());
        $data = $this->getResponseData($response);

        $this->assertArrayHasKey('token', $data);

        return [
            'userId' => $user['userId'],
            'token' => $data['token'],
        ];
    }

    #[Depends('testAuthenticate')]
    public function testGetUser(array $context): void
    {
        $response = $this->jsonRequest('GET', '/users/'.$context['userId']);

        $this->assertEquals(Response::HTTP_OK, $response->getStatusCode());

        $data = $this->getResponseData($response);

        $this->assertEquals($context['userId'], $data['id']);
    }

    #[Depends('testCreateUser')]
    public function testUpdateUser(array $context): array
    {
        $payload = [
            'name' => 'Updated Name',
            'email' => 'updated-'.$this->faker->email(),
        ];

        $response = $this->jsonRequest('PUT', '/users/'.$context['userId'], $payload);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $this->getResponseData($response);

        $this->assertEquals($payload['name'], $data['name']);
        $this->assertEquals($payload['email'], $data['email']);

        return [
            'userId' => $context['userId'],
            'email' => $payload['email'],
            'password' => $context['password'],
        ];
    }

    #[Depends('testCreateUser')]
    public function testPatchUser(array $context): void
    {
        $payload = [
            'name' => 'Partially Patched Name',
        ];

        $response = $this->jsonRequest('PATCH', '/users/'.$context['userId'], $payload);

        $this->assertEquals(200, $response->getStatusCode());
        $data = $this->getResponseData($response);

        $this->assertEquals($payload['name'], $data['name']);
    }

    #[Depends('testUpdateUser')]
    public function testPasswordReset(array $context): void
    {
        $newPassword = $this->faker->password(12);

        $response = $this->jsonRequest('POST', '/users/'.$context['userId'].'/password-reset', [
            'new_password' => $newPassword,
        ]);

        $this->assertEquals(204, $response->getStatusCode());

        $login = $this->jsonRequest('POST', '/login', [
            'email' => $context['email'],
            'password' => $newPassword,
        ]);

        $this->assertEquals(200, $login->getStatusCode());
        $loginData = $this->getResponseData($login);

        $this->assertArrayHasKey('token', $loginData);
    }

    public function testListUsers(): void
    {
        $response = $this->jsonRequest('GET', '/users?offset=0&limit=10');

        $this->assertEquals(200, $response->getStatusCode());
        $data = $this->getResponseData($response);

        $this->assertArrayHasKey('data', $data);
        $this->assertArrayHasKey('meta', $data);
        $this->assertIsArray($data['data']);
        $this->assertArrayHasKey('offset', $data['meta']);
        $this->assertArrayHasKey('limit', $data['meta']);
        $this->assertArrayHasKey('count', $data['meta']);
    }

    #[Depends('testCreateUser')]
    public function testDeleteUser(array $context): void
    {
        $response = $this->jsonRequest('DELETE', '/users/'.$context['userId']);
        $this->assertEquals(Response::HTTP_NO_CONTENT, $response->getStatusCode());
    }
}
