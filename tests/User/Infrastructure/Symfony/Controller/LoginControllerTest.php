<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\LoginCommand;
use App\User\Infrastructure\Symfony\Controller\LoginController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class LoginControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private LoginController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new LoginController($this->commandBus);
    }

    public function testItReturnsTokenForValidJsonRequest(): void
    {
        $email = $this->faker->email();
        $password = $this->faker->password();
        $token = $this->faker->sha256();

        $request = new Request(
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => $email, 'password' => $password])
        );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with(new LoginCommand($email, $password))
            ->willReturn($token);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($token, $data['token']);
    }

    public function testItReturnsTokenForValidFormData(): void
    {
        $email = $this->faker->email();
        $password = $this->faker->password();
        $token = $this->faker->sha256();

        $request = new Request(
            request: ['email' => $email, 'password' => $password],
            server: ['CONTENT_TYPE' => 'application/x-www-form-urlencoded']
        );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with(new LoginCommand($email, $password))
            ->willReturn($token);

        $response = $this->controller->__invoke($request);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($token, $data['token']);
    }

    public function testItThrowsExceptionWithInvalidJson(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid request payload.');

        $request = new Request(
            server: ['CONTENT_TYPE' => 'application/json'],
            content: 'not a json string'
        );

        $this->controller->__invoke($request);
    }

    public function testItThrowsExceptionWhenFieldsAreMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Email and password are required.');

        $request = new Request(
            server: ['CONTENT_TYPE' => 'application/json'],
            content: json_encode(['email' => $this->faker->email()])
        );

        $this->controller->__invoke($request);
    }
}
