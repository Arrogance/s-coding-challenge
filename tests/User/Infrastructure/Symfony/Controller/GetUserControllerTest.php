<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\GetUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Infrastructure\Symfony\Controller\GetUserController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class GetUserControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private GetUserController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new GetUserController($this->commandBus);
    }

    public function testItReturnsUserSuccessfully(): void
    {
        $userId = $this->faker->uuid();
        $name = $this->faker->name();
        $email = $this->faker->email();
        $password = $this->faker->password();

        $user = new User(
            new UserId($userId),
            $name,
            new Email($email),
            new Password($password)
        );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(fn (GetUserCommand $command) => $command->id === $userId))
            ->willReturn($user);

        $response = $this->controller->__invoke($userId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $this->assertEquals($name, $data['name']);
        $this->assertEquals($email, $data['email']);
        $this->assertEquals($userId, $data['id']);
    }
}
