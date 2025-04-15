<?php

declare(strict_types=1);

namespace App\Common\Domain\Event;

class UserDeletedEvent extends Event
{
    public function __construct(
        public readonly string $id,
        public readonly string $deletedAt,
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }
}
