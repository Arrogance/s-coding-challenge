<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Subscriber;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Application\Subscriber\Subscriber;
use App\Common\Domain\Event\Event;
use App\Common\Domain\Event\UserDeletedEvent;
use App\WorkEntry\Application\Command\DeleteWorkEntryByUserCommand;
use Psr\Log\LoggerInterface;

class UserDeletedEventSubscriber extends Subscriber
{
    public function __construct(
        private readonly CommandBusInterface $commandBus,
        private readonly LoggerInterface $logger
    ) {
    }

    public function handle(Event|UserDeletedEvent $event): void
    {
        $this->logger->info('UserDeletedEvent event received: {event}', ['event' => $event]);

        $command = new DeleteWorkEntryByUserCommand(
            $event->id,
        );

        $this->commandBus->send($command);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            UserDeletedEvent::class => 10,
        ];
    }
}
