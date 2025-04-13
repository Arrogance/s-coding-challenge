<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\Common\Domain\Event\Event;

class UserCreatedEvent extends Event
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $createdAt,
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }
}
