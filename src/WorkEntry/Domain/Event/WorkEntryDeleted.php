<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Event;

use App\Common\Domain\Event\Event;

class WorkEntryDeleted extends Event
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly \DateTimeImmutable $deletedAt,
    ) {
    }
}
