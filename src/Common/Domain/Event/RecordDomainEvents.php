<?php

declare(strict_types=1);

namespace App\Common\Domain\Event;

trait RecordDomainEvents
{
    private array $domainEvents = [];

    /**
     * Record a domain event.
     */
    protected function recordEvent(Event $event): void
    {
        $this->domainEvents[] = $event;
    }

    /**
     * Get all recorded domain events and clear the internal collection.
     *
     * @return Event[]
     */
    public function pullDomainEvents(): array
    {
        $events = $this->domainEvents;
        $this->domainEvents = [];

        return $events;
    }

    /**
     * Check if there are any recorded events.
     */
    public function hasRecordedEvents(): bool
    {
        return \count($this->domainEvents) > 0;
    }
}
