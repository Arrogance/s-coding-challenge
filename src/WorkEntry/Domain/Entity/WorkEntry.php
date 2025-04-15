<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Entity;

use App\Common\Domain\Event\RecordDomainEvents;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Domain\Event\WorkEntryCreated;
use App\WorkEntry\Domain\Event\WorkEntryDeleted;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;

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

    public function setStartDate(\DateTimeImmutable $startDate): void
    {
        $this->startDate = $startDate;
        $this->update();
    }

    public function endDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function setEndDate(\DateTimeImmutable $endDate): void
    {
        $this->endDate = $endDate;
        $this->update();
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

    public function delete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
        $this->update();

        $this->recordEvent(new WorkEntryDeleted(
            id: $this->id->value(),
            userId: $this->userId->value(),
            deletedAt: $this->deletedAt,
        ));
    }

    protected function update(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
