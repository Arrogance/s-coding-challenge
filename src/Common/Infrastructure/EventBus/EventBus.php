<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\EventBus;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Application\Subscriber\Subscriber;
use App\Common\Domain\Event\Event;
use Symfony\Component\Messenger\MessageBusInterface;

class EventBus implements EventBusInterface
{
    private array $subscribers = [];

    public function __construct(
        private readonly MessageBusInterface $messageBus
    ) {
    }

    public function dispatch(Event $event): void
    {
        $eventClass = $event::class;

        if (!isset($this->subscribers[$eventClass])) {
            return;
        }

        ksort($this->subscribers[$eventClass], \SORT_NUMERIC);

        foreach ($this->subscribers[$eventClass] as $subscriberList) {
            foreach ($subscriberList as $subscriber) {
                if ($event->stopped) {
                    break 2;
                }

                if ($event->hasSubscriberBeenPassed($subscriber::class)) {
                    continue;
                }

                if ($event->isAsynchronous()) {
                    $this->sendAsync($event);

                    return;
                }

                $event->markSubscriberAsPassed($subscriber::class);
                $subscriber->handle($event);
            }
        }
    }

    private function sendAsync(Event $event): void
    {
        $this->messageBus->dispatch($event);
    }

    public function registerSubscribers(Subscriber ...$subscribers): void
    {
        foreach ($subscribers as $subscriber) {
            foreach ($subscriber::getSubscribedEvents() as $event => $priority) {
                $this->subscribers[$event][$priority][] = $subscriber;
            }
        }
    }
}
