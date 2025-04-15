<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\UpdateUserCommand;
use App\User\Application\Handler\UpdateUserHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class UpdateUserHandlerTest extends TestCase
{
    public function testItUpdatesUserAndDispatchesEvents(): void
    {
        $faker = Factory::create();

        $userId = UserId::generate();
        $originalName = $faker->name();
        $originalEmail = new Email($faker->unique()->safeEmail());
        $originalPassword = new Password($faker->password());

        $newName = $faker->name();
        $newEmail = $faker->unique()->safeEmail();

        $user = new User($userId, $originalName, $originalEmail, $originalPassword);
        $user->pullDomainEvents();

        $command = new UpdateUserCommand(
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

        $handler = new UpdateUserHandler($repository, $eventBus);

        $updatedUser = $handler->handle($command);

        $this->assertSame($newName, $updatedUser->name());
        $this->assertSame($newEmail, (string) $updatedUser->email());
    }
}
