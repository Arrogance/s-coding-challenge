<?php

declare(strict_types=1);

namespace App\User\Domain\Event;

use App\Common\Domain\Event\Event;
use App\Common\Domain\ValueObject\UserId;

abstract class UserEvent extends Event
{
    public function __construct(
        protected UserId $id
    ) {
        $this->occurredOn = new \DateTimeImmutable();
    }

    public function id(): UserId
    {
        return $this->id;
    }
}
