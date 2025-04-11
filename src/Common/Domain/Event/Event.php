<?php

declare(strict_types=1);

namespace App\Common\Domain\Event;

abstract class Event
{
    /** Events processed within the same thread/request. */
    public const int TYPE_SYNC = 1;

    /** Events dispatched asynchronously via an Event Bus consumer. */
    public const int TYPE_ASYNC = 2;

    public bool $stopped = false {
        get {
            return $this->stopped;
        }
    }

    public \DateTimeImmutable $occurredOn {
        get {
            return $this->occurredOn;
        }
        set {
            $this->occurredOn = $value ?? new \DateTimeImmutable();
        }
    }

    private int $type = self::TYPE_SYNC;

    /** @var array<string> */
    private array $passedSubscribers = [];

    public function stop(): void
    {
        $this->stopped = true;
    }

    public function markAsSynchronous(): void
    {
        $this->type = self::TYPE_SYNC;
    }

    public function isSynchronous(): bool
    {
        return self::TYPE_SYNC === $this->type;
    }

    public function markAsAsynchronous(): void
    {
        $this->type = self::TYPE_ASYNC;
    }

    public function isAsynchronous(): bool
    {
        return self::TYPE_ASYNC === $this->type;
    }

    public function markSubscriberAsPassed(string $subscriber): void
    {
        $this->passedSubscribers[$subscriber] = true;
    }

    public function hasSubscriberBeenPassed(string $subscriber): bool
    {
        return isset($this->passedSubscribers[$subscriber]);
    }
}
