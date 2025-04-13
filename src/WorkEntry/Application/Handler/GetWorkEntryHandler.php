<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\GetWorkEntryCommand;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;

class GetWorkEntryHandler extends Handler
{
    public function __construct(private readonly WorkEntryRepositoryInterface $workEntryRepository)
    {
    }

    public function handle(Command|GetWorkEntryCommand $command): ?WorkEntry
    {
        return $this->workEntryRepository->findById(
            userId: new UserId($command->userId),
            id: new WorkEntryId($command->id),
        );
    }

    public static function getHandledCommand(): string
    {
        return GetWorkEntryCommand::class;
    }
}
