<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Event;

use App\Common\Domain\Event\Event;
use App\Common\Domain\ValueObject\WorkEntryId;

abstract class WorkEntryEvent extends Event
{
    public function __construct(
        protected WorkEntryId $id
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function id(): WorkEntryId
    {
        return $this->id;
    }
}
