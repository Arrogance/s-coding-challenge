<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Entity;

use App\Common\Domain\Event\RecordDomainEvents;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Domain\ValueObject\WorkEntryId;
use App\WorkEntry\Domain\Event\WorkEntryCreated;

class WorkEntry
{
    use RecordDomainEvents;

    private \DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(
        private readonly WorkEntryId $id,
        private readonly UserId $userId,
        private \DateTimeImmutable $startDate,
        private \DateTimeImmutable $endDate,
        private readonly \DateTimeImmutable $createdAt = new \DateTimeImmutable(),
    ) {
        $this->updatedAt = new \DateTimeImmutable();

        $this->recordEvent(new WorkEntryCreated(
            $this->id,
            $this->userId,
            $this->startDate,
            $this->endDate
        ));
    }

    public function id(): WorkEntryId
    {
        return $this->id;
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

    public function createdAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function updatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function deletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    protected function update(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
