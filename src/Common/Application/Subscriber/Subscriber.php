<?php

declare(strict_types=1);

namespace App\Common\Application\Subscriber;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\Event\Event;

abstract class Subscriber
{
    abstract public function handle(Event $event): void;

    abstract public static function getSubscribedEvents(): array;

    public function register(EventBusInterface $eventBus): void
    {
        $eventBus->register($this);
    }
}
