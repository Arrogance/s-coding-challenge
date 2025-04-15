<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\Event\UserDeletedEvent;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\DeleteUserCommand;
use App\User\Application\Handler\DeleteUserHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class DeleteUserHandlerTest extends TestCase
{
    public function testItDeletesUserAndDispatchesEvent(): void
    {
        $faker = Factory::create();
        $id = UserId::generate();
        $name = $faker->name();
        $email = new Email($faker->unique()->safeEmail());
        $password = new Password($faker->password());

        $user = new User($id, $name, $email, $password);
        $user->pullDomainEvents();

        $command = new DeleteUserCommand($id->value());

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
                   ->method('findById')
                   ->with($id)
                   ->willReturn($user);

        $repository->expects($this->once())
                   ->method('save')
                   ->with($user);

        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->atLeastOnce())
                 ->method('dispatch')
                 ->with($this->isInstanceOf(UserDeletedEvent::class));

        $handler = new DeleteUserHandler(
            userRepository: $repository,
            eventBus: $eventBus
        );

        $deletedUser = $handler->handle($command);

        $this->assertInstanceOf(User::class, $deletedUser);
        $this->assertEquals($id->value(), $deletedUser->id()->value());
    }
}
