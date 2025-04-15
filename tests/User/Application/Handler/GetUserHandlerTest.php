<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\GetUserCommand;
use App\User\Application\Handler\GetUserHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class GetUserHandlerTest extends TestCase
{
    public function testItReturnsTheUserById(): void
    {
        $faker = Factory::create();

        $userId = UserId::generate();
        $name = $faker->name();
        $email = new Email($faker->unique()->safeEmail());
        $password = new Password('$2y$dummy');

        $user = new User($userId, $name, $email, $password);
        $command = new GetUserCommand($userId->value());

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
                   ->method('findById')
                   ->with($userId)
                   ->willReturn($user);

        $handler = new GetUserHandler($repository);

        $result = $handler->handle($command);

        $this->assertInstanceOf(User::class, $result);
        $this->assertSame($user->id()->value(), $result->id()->value());
        $this->assertSame((string) $user->email(), (string) $result->email());
    }
}
