<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\GetUserCommand;
use App\User\Domain\Repository\UserRepositoryInterface;

final class GetUserHandler extends Handler
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function handle(Command|GetUserCommand $command)
    {
        return $this->userRepository->findById(new UserId($command->id));
    }

    public static function getHandledCommand(): string
    {
        return GetUserCommand::class;
    }
}
