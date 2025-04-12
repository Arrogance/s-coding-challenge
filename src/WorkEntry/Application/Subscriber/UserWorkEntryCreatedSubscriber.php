<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Subscriber;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Application\Subscriber\Subscriber;
use App\Common\Contract\Event\UserWorkEntryCreatedEvent;
use App\Common\Domain\Event\Event;
use App\WorkEntry\Application\Command\CreateWorkEntryCommand;
use Psr\Log\LoggerInterface;

class UserWorkEntryCreatedSubscriber extends Subscriber
{
    public function __construct(private readonly CommandBusInterface $commandBus, private readonly LoggerInterface $logger)
    {
    }

    public function handle(Event|UserWorkEntryCreatedEvent $event): void
    {
        $this->logger->info('UserWorkEntryCreatedEvent event received: {event}', ['event' => $event]);

        $command = new CreateWorkEntryCommand(
            $event->workEntryId,
            $event->userId,
            $event->startDate,
            $event->endDate
        );

        $this->commandBus->send($command);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserWorkEntryCreatedEvent::class => 10,
        ];
    }
}
