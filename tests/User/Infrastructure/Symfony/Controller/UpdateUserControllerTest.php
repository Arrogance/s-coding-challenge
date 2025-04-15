<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\UpdateUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Infrastructure\Symfony\Controller\UpdateUserController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class UpdateUserControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private UpdateUserController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new UpdateUserController($this->commandBus);
    }

    public function testItUpdatesUserSuccessfully(): void
    {
        $userId = $this->faker->uuid();
        $name = $this->faker->name();
        $email = $this->faker->email();
        $password = $this->faker->password();

        $payload = ['name' => $name, 'email' => $email];
        $request = new Request(content: json_encode($payload));

        $user = new User(
            new UserId($userId),
            $name,
            new Email($email),
            new Password($password)
        );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (UpdateUserCommand $cmd) => $cmd->id === $userId
                && $cmd->name === $name
                && $cmd->email === $email
            ))
            ->willReturn($user);

        $response = $this->controller->__invoke($request, $userId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($userId, $data['id']);
        $this->assertEquals($name, $data['name']);
        $this->assertEquals($email, $data['email']);
    }

    public function testItThrowsExceptionWithInvalidJson(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid JSON payload.');

        $request = new Request(content: 'not valid json');
        $this->controller->__invoke($request, $this->faker->uuid());
    }

    public function testItThrowsExceptionWhenNameIsMissing(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("Missing or invalid 'name'");

        $request = new Request(content: json_encode([
            'email' => $this->faker->email(),
        ]));

        $this->controller->__invoke($request, $this->faker->uuid());
    }

    public function testItThrowsExceptionWhenEmailIsNotString(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("Missing or invalid 'email'");

        $request = new Request(content: json_encode([
            'name' => $this->faker->name(),
            'email' => ['invalid'],
        ]));

        $this->controller->__invoke($request, $this->faker->uuid());
    }
}
