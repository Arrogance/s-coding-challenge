<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Security\TokenManagerInterface;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\LoginCommand;
use App\User\Application\Handler\LoginHandler;
use App\User\Application\Service\PasswordHasherService;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\InvalidCredentialsException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class LoginHandlerTest extends TestCase
{
    public function testItLogsInSuccessfullyAndReturnsToken(): void
    {
        $faker = Factory::create();

        $email = new Email($faker->unique()->safeEmail());
        $plainPassword = $faker->password();
        $hashedPassword = new Password($plainPassword);
        $userId = UserId::generate();

        $user = new User($userId, $faker->name(), $email, $hashedPassword);
        $user->pullDomainEvents();

        $command = new LoginCommand($email->value(), $plainPassword);

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
                   ->method('findByEmail')
                   ->with($email)
                   ->willReturn($user);

        $hasher = $this->createMock(PasswordHasherService::class);
        $hasher->expects($this->once())
               ->method('verify')
               ->with($plainPassword, $hashedPassword)
               ->willReturn(true);

        $eventBus = $this->createMock(EventBusInterface::class);
        $eventBus->expects($this->atLeast(0))
                 ->method('dispatch');

        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->expects($this->once())
                     ->method('generateToken')
                     ->with($userId->value())
                     ->willReturn('fake.jwt.token');

        $handler = new LoginHandler(
            userRepository: $repository,
            hasher: $hasher,
            tokenManager: $tokenManager,
            eventBus: $eventBus
        );

        $token = $handler->handle($command);

        $this->assertEquals('fake.jwt.token', $token);
    }

    public function testItThrowsOnInvalidCredentials(): void
    {
        $faker = Factory::create();

        $command = new LoginCommand(
            email: $faker->unique()->safeEmail(),
            password: 'invalid-password'
        );

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->method('findByEmail')->willReturn(null);

        $hasher = $this->createMock(PasswordHasherService::class);
        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $eventBus = $this->createMock(EventBusInterface::class);

        $handler = new LoginHandler($repository, $hasher, $tokenManager, $eventBus);

        $this->expectException(InvalidCredentialsException::class);

        $handler->handle($command);
    }
}
