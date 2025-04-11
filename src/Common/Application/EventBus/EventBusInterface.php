<?php

declare(strict_types=1);

namespace App\Common\Application\EventBus;

use App\Common\Application\Subscriber\Subscriber;
use App\Common\Domain\Event\Event;

interface EventBusInterface
{
    public function dispatch(Event $event): void;

    public function registerSubscribers(Subscriber ...$subscriber): void;
}
