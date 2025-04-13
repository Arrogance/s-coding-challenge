<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Event;

use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;

class WorkEntryCreated extends WorkEntryEvent
{
    public function __construct(
        WorkEntryId $id,
        private readonly UserId $userId,
        private readonly \DateTimeImmutable $startDate,
        private readonly \DateTimeImmutable $endDate
    ) {
        parent::__construct($id);
    }

    public function userId(): UserId
    {
        return $this->userId;
    }

    public function startDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function endDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }
}
