<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\DeleteWorkEntryByUserCommand;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;

class DeleteWorkEntryByUserHandler extends Handler
{
    public function __construct(
        private readonly WorkEntryRepositoryInterface $workEntryRepository,
        private readonly EventBusInterface $eventBus
    ) {
    }

    public function handle(Command|DeleteWorkEntryByUserCommand $command): void
    {
        $userId = new UserId($command->userId);

        $this->workEntryRepository->beginTransaction();

        try {
            $batch = [];
            $counter = 0;
            $limit = 100;

            foreach ($this->workEntryRepository->iterateActiveByUser($userId, $limit) as $entry) {
                $entry->delete();

                $isLastOfBatch = 0 === ++$counter % $limit;

                $this->workEntryRepository->save($entry, flush: $isLastOfBatch);
                $batch[] = $entry;

                if ($isLastOfBatch) {
                    foreach ($batch as $e) {
                        foreach ($e->pullDomainEvents() as $event) {
                            $this->eventBus->dispatch($event);
                        }
                    }

                    $batch = [];
                }
            }

            if (!empty($batch)) {
                $last = array_pop($batch);
                $this->workEntryRepository->save($last);

                foreach ([$last, ...$batch] as $entry) {
                    foreach ($entry->pullDomainEvents() as $event) {
                        $this->eventBus->dispatch($event);
                    }
                }
            }

            $this->workEntryRepository->commit();
        } catch (\Throwable $e) {
            $this->workEntryRepository->rollback();
            throw $e;
        }
    }

    public static function getHandledCommand(): string
    {
        return DeleteWorkEntryByUserCommand::class;
    }
}
