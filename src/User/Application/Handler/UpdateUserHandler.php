<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\UpdateUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;

class UpdateUserHandler extends Handler
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function handle(Command|UpdateUserCommand $command): User
    {
        $userId = new UserId($command->id);

        $user = $this->userRepository->findById($userId);

        $user->rename($command->name);
        $user->changeEmail(new Email($command->email));

        $this->userRepository->save($user);

        return $user;
    }

    public static function getHandledCommand(): string
    {
        return UpdateUserCommand::class;
    }
}
