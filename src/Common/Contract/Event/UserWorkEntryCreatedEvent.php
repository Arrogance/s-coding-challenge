<?php

declare(strict_types=1);

namespace App\Common\Contract\Event;

use App\Common\Domain\Event\Event;

final class UserWorkEntryCreatedEvent extends Event
{
    public function __construct(
        public readonly string $workEntryId,
        public readonly string $userId,
        public readonly \DateTimeImmutable $startDate,
        public readonly \DateTimeImmutable $endDate
    ) {
        $this->markAsAsynchronous();
        $this->occurredOn = new \DateTimeImmutable();
    }
}
