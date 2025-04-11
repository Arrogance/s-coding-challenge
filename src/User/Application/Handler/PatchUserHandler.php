<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\PatchUserCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;

class PatchUserHandler extends Handler
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function handle(Command|PatchUserCommand $command): User
    {
        $user = $this->userRepository->findById(new UserId($command->id));

        if (null !== $command->name) {
            $user->rename($command->name);
        }

        if (null !== $command->email) {
            $user->changeEmail(new Email($command->email));
        }

        $this->userRepository->save($user);

        return $user;
    }

    public static function getHandledCommand(): string
    {
        return PatchUserCommand::class;
    }
}
