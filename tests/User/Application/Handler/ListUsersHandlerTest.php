<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Handler;

use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\ListUsersCommand;
use App\User\Application\Handler\ListUsersHandler;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class ListUsersHandlerTest extends TestCase
{
    public function testItReturnsPaginatedUsers(): void
    {
        $faker = Factory::create();

        $users = [];
        for ($i = 0; $i < 3; ++$i) {
            $users[] = new User(
                UserId::generate(),
                $faker->name(),
                new Email($faker->unique()->safeEmail()),
                new Password('$2y$dummy')
            );
        }

        $command = new ListUsersCommand(offset: 0, limit: 10);

        $repository = $this->createMock(UserRepositoryInterface::class);
        $repository->expects($this->once())
                   ->method('findPaginated')
                   ->with(0, 10)
                   ->willReturn($users);

        $handler = new ListUsersHandler($repository);

        $result = $handler->handle($command);

        $this->assertIsIterable($result);
        $this->assertCount(3, $result);
        foreach ($result as $user) {
            $this->assertInstanceOf(User::class, $user);
        }
    }
}
