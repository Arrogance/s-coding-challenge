<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Handler\CreateUserHandler;
use App\User\Application\Service\PasswordHasherService;
use App\User\Domain\Entity\User;
use App\User\Domain\Event\UserCreatedEvent;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class CreateUserHandlerTest extends TestCase
{
    public function testItCreatesAUserAndDispatchesEvent(): void
    {
        $faker = Factory::create();

        $name = $faker->name();
        $email = $faker->unique()->safeEmail();
        $plainPassword = $faker->password();

        $command = new CreateUserCommand(
            name: $name,
            email: $email,
            password: $plainPassword
        );

        $hashedPassword = new Password($plainPassword);

        $hasher = $this->createMock(PasswordHasherService::class);
        $hasher->expects($this->once())
               ->method('hash')
               ->with($plainPassword)
               ->willReturn($hashedPassword);

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
                   ->method('save')
                   ->with($this->isInstanceOf(User::class));

        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->atLeastOnce())
                 ->method('dispatch')
                 ->with($this->isInstanceOf(UserCreatedEvent::class));

        $handler = new CreateUserHandler(
            passwordHasher: $hasher,
            userRepository: $repository,
            eventBus: $eventBus
        );

        $user = $handler->handle($command);

        $this->assertInstanceOf(User::class, $user);
        $this->assertSame($email, (string) $user->email());
    }
}
