<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\DeleteWorkEntryCommand;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;

class DeleteWorkEntryHandler extends Handler
{
    public function __construct(
        private readonly WorkEntryRepositoryInterface $workEntryRepository,
        private readonly EventBusInterface $eventBus
    ) {
    }

    public function handle(Command|DeleteWorkEntryCommand $command): WorkEntry
    {
        $userId = new UserId($command->userId);
        $workEntryId = new WorkEntryId($command->id);

        $workEntry = $this->workEntryRepository->findById(
            userId: $userId,
            id: $workEntryId
        );

        $workEntry->delete();

        $this->workEntryRepository->save($workEntry);

        foreach ($workEntry->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return $workEntry;
    }

    public static function getHandledCommand(): string
    {
        return DeleteWorkEntryCommand::class;
    }
}
