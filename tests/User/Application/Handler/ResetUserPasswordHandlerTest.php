<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\ResetUserPasswordCommand;
use App\User\Application\Handler\ResetUserPasswordHandler;
use App\User\Application\Service\PasswordHasherService;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class ResetUserPasswordHandlerTest extends TestCase
{
    public function testItResetsUserPasswordAndDispatchesEvent(): void
    {
        $faker = Factory::create();

        $userId = UserId::generate();
        $email = new Email($faker->unique()->safeEmail());
        $name = $faker->name();
        $originalPassword = new Password($faker->password());
        $newPlainPassword = $faker->password();
        $newHashedPassword = new Password($newPlainPassword);

        $user = new User($userId, $name, $email, $originalPassword);
        $user->pullDomainEvents(); // limpiar eventos previos

        $command = new ResetUserPasswordCommand($userId->value(), $newPlainPassword);

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
                   ->method('findById')
                   ->with($userId)
                   ->willReturn($user);

        $repository->expects($this->once())
                   ->method('save')
                   ->with($user);

        $hasher = $this->createMock(PasswordHasherService::class);
        $hasher->expects($this->once())
               ->method('hash')
               ->with($newPlainPassword)
               ->willReturn($newHashedPassword);

        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->never())
                 ->method('dispatch');

        $handler = new ResetUserPasswordHandler(
            userRepository: $repository,
            hasher: $hasher,
            eventBus: $eventBus
        );

        $updatedUser = $handler->handle($command);

        $this->assertInstanceOf(User::class, $updatedUser);
        $this->assertSame($userId->value(), $updatedUser->id()->value());
        $this->assertSame($email->value(), (string) $updatedUser->email());
    }
}
