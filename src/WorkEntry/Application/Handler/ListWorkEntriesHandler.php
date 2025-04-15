<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\ListWorkEntriesCommand;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;

class ListWorkEntriesHandler extends Handler
{
    public function __construct(private readonly WorkEntryRepositoryInterface $workEntryRepository)
    {
    }

    public function handle(Command|ListWorkEntriesCommand $command): iterable
    {
        return $this->workEntryRepository->findPaginated(
            userId: new UserId($command->userId),
            offset: $command->offset,
            limit: $command->limit
        );
    }

    public static function getHandledCommand(): string
    {
        return ListWorkEntriesCommand::class;
    }
}
