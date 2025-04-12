<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Domain\ValueObject\WorkEntryId;
use App\WorkEntry\Application\Command\CreateWorkEntryCommand;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;

final class CreateWorkEntryHandler extends Handler
{
    public function __construct(
        private readonly WorkEntryRepositoryInterface $workEntryRepository,
        private readonly EventBusInterface $eventBus,
    ) {
    }

    public function handle(Command|CreateWorkEntryCommand $command): WorkEntry
    {
        $workEntry = new WorkEntry(
            new WorkEntryId($command->id),
            new UserId($command->userId),
            $command->startDate,
            $command->endDate
        );

        $this->workEntryRepository->save($workEntry);

        foreach ($workEntry->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return $workEntry;
    }

    public static function getHandledCommand(): string
    {
        return CreateWorkEntryCommand::class;
    }
}
