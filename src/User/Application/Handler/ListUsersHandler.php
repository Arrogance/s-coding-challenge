<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\Handler\Handler;
use App\User\Application\Command\ListUsersCommand;
use App\User\Domain\Repository\UserRepositoryInterface;

final class ListUsersHandler extends Handler
{
    public function __construct(private readonly UserRepositoryInterface $userRepository)
    {
    }

    public function handle(Command|ListUsersCommand $command): iterable
    {
        return $this->userRepository->findPaginated(
            offset: $command->offset,
            limit: $command->limit
        );
    }

    public static function getHandledCommand(): string
    {
        return ListUsersCommand::class;
    }
}
