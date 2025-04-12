<?php

declare(strict_types=1);

namespace App\User\Application\Handler;

use App\Common\Application\Command\Command;
use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Handler\Handler;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Domain\ValueObject\WorkEntryId;
use App\User\Application\Command\CreateWorkEntryCommand;
use App\User\Domain\Repository\UserRepositoryInterface;

final class CreateWorkEntryHandler extends Handler
{
    public function __construct(
        private readonly UserRepositoryInterface $userRepository,
        private readonly EventBusInterface $eventBus
    ) {
    }

    public function handle(Command|CreateWorkEntryCommand $command): string
    {
        $user = $this->userRepository->findById(new UserId($command->id));

        $workEntryId = WorkEntryId::generate();
        $user->workEntry($workEntryId, $command->startDate, $command->endDate);

        foreach ($user->pullDomainEvents() as $event) {
            $this->eventBus->dispatch($event);
        }

        return $workEntryId->value();
    }

    public static function getHandledCommand(): string
    {
        return CreateWorkEntryCommand::class;
    }
}
