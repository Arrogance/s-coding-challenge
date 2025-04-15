<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\PatchUserCommand;
use App\User\Application\Handler\PatchUserHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class PatchUserHandlerTest extends TestCase
{
    public function testItPartiallyUpdatesUserAndDispatchesEvents(): void
    {
        $faker = Factory::create();

        $userId = UserId::generate();
        $originalEmail = new Email($faker->unique()->safeEmail());
        $originalName = $faker->name();
        $newEmail = $faker->unique()->safeEmail();
        $newName = $faker->name();

        $user = new User(
            id: $userId,
            name: $originalName,
            email: $originalEmail,
            password: new Password('$2y$dummy')
        );

        $user->pullDomainEvents();

        $command = new PatchUserCommand(
            id: $userId->value(),
            name: $newName,
            email: $newEmail
        );

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
                   ->method('findById')
                   ->with($userId)
                   ->willReturn($user);

        $repository->expects($this->once())
                   ->method('save')
                   ->with($user);

        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->never())
                 ->method('dispatch');

        $handler = new PatchUserHandler($repository, $eventBus);

        $updatedUser = $handler->handle($command);

        $this->assertSame($newName, $updatedUser->name());
        $this->assertSame($newEmail, (string) $updatedUser->email());
    }

    public function testItDoesNothingIfFieldsAreNull(): void
    {
        $faker = Factory::create();

        $userId = UserId::generate();
        $email = new Email($faker->unique()->safeEmail());
        $name = $faker->name();

        $user = new User(
            id: $userId,
            name: $name,
            email: $email,
            password: new Password('$2y$dummy')
        );

        $user->pullDomainEvents();

        $command = new PatchUserCommand(
            id: $userId->value(),
            name: null,
            email: null
        );

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('findById')->willReturn($user);

        $repository->expects($this->once())->method('save')->with($user);

        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->never())->method('dispatch');

        $handler = new PatchUserHandler($repository, $eventBus);
        $result = $handler->handle($command);

        $this->assertSame($name, $result->name());
        $this->assertSame((string) $email, (string) $result->email());
    }
}
