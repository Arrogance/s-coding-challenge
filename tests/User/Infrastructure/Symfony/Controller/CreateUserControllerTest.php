<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\CreateUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Infrastructure\Symfony\Controller\CreateUserController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CreateUserControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private CreateUserController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new CreateUserController($this->commandBus);
    }

    public function testItCreatesUserSuccessfully(): void
    {
        $payload = [
            'name' => $this->faker->name(),
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ];

        $request = new Request(content: json_encode($payload));

        $user = new User(
            new UserId($this->faker->uuid()),
            $payload['name'],
            new Email($payload['email']),
            new Password($payload['password'])
        );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(fn (CreateUserCommand $command) => $command->name === $payload['name']
                    && $command->email === $payload['email']
                    && $command->password === $payload['password']))
            ->willReturn($user);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($payload['name'], $data['name']);
        $this->assertEquals($payload['email'], $data['email']);
        $this->assertArrayHasKey('id', $data);
    }

    public function testItThrowsExceptionWithInvalidJson(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid JSON payload.');

        $request = new Request(content: 'not json');
        $this->controller->__invoke($request);
    }

    public function testItThrowsExceptionWhenRequiredFieldsAreMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("Missing or invalid 'email'");

        $request = new Request(content: json_encode([
            'name' => $this->faker->name(),
            'password' => $this->faker->password(),
        ]));

        $this->controller->__invoke($request);
    }

    public function testItThrowsExceptionWhenFieldsAreNotStrings(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("Missing or invalid 'name'");

        $request = new Request(content: json_encode([
            'name' => ['array'],
            'email' => $this->faker->email(),
            'password' => $this->faker->password(),
        ]));

        $this->controller->__invoke($request);
    }
}
