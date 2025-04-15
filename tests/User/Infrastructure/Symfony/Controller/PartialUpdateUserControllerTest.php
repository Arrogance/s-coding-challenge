<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\PatchUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Infrastructure\Symfony\Controller\PartialUpdateUserController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PartialUpdateUserControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private PartialUpdateUserController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new PartialUpdateUserController($this->commandBus);
    }

    public function testItUpdatesUserPartiallyWithName(): void
    {
        $userId = $this->faker->uuid();
        $name = $this->faker->name();
        $email = $this->faker->email();
        $password = $this->faker->password();

        $payload = ['name' => $name];
        $request = new Request(content: json_encode($payload));

        $user = new User(new UserId($userId), $name, new Email($email), new Password($password));

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (PatchUserCommand $cmd) => $cmd->id === $userId
                && $cmd->name === $name
                && null === $cmd->email
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

    public function testItUpdatesUserPartiallyWithEmail(): void
    {
        $userId = $this->faker->uuid();
        $email = $this->faker->email();
        $name = $this->faker->name();
        $password = $this->faker->password();

        $payload = ['email' => $email];
        $request = new Request(content: json_encode($payload));

        $user = new User(new UserId($userId), $name, new Email($email), new Password($password));

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (PatchUserCommand $cmd) => $cmd->id === $userId
                && null === $cmd->name
                && $cmd->email === $email
            ))
            ->willReturn($user);

        $response = $this->controller->__invoke($request, $userId);

        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($email, $data['email']);
    }

    public function testItThrowsExceptionWhenPayloadIsInvalidJson(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid JSON payload.');

        $request = new Request(content: 'invalid json');
        $this->controller->__invoke($request, $this->faker->uuid());
    }

    public function testItThrowsExceptionWhenNoValidFieldIsPresent(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("At least one field must be provided: 'name', 'email'");

        $request = new Request(content: json_encode(['foo' => 'bar']));
        $this->controller->__invoke($request, $this->faker->uuid());
    }
}
