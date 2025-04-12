<?php

declare(strict_types=1);

namespace App\Common\Application\Subscriber;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\Event\Event;
use Psr\Log\LoggerInterface;

final class AsyncEventSubscriber extends Subscriber
{
    public function __construct(private readonly EventBusInterface $eventBus, private readonly LoggerInterface $logger)
    {
    }

    public function __invoke(Event $event): void
    {
        $this->handle($event);
    }

    public function handle(Event $event): void
    {
        $event->markAsSynchronous();

        $this->logger->info('Async event received: {event}', ['event' => $event]);

        $this->eventBus->dispatch($event);
    }

    public static function getSubscribedEvents(): array
    {
        return [
            Event::class => 0,
        ];
    }
}
