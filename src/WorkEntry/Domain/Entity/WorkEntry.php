<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Entity;

use App\Common\Domain\Event\RecordDomainEvents;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Domain\Event\WorkEntryCreated;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;

class WorkEntry
{
    use RecordDomainEvents;

    private \DateTimeImmutable $updatedAt;
    private ?\DateTimeImmutable $deletedAt = null;

    public function __construct(
        private readonly WorkEntryId $id, // or use a WorkEntryId VO
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

    public function getId(): WorkEntryId
    {
        return $this->id;
    }

    public function getUserId(): UserId
    {
        return $this->userId;
    }

    public function getStartDate(): \DateTimeImmutable
    {
        return $this->startDate;
    }

    public function getEndDate(): \DateTimeImmutable
    {
        return $this->endDate;
    }

    public function getCreatedAt(): \DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getUpdatedAt(): \DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function getDeletedAt(): ?\DateTimeImmutable
    {
        return $this->deletedAt;
    }

    public function isDeleted(): bool
    {
        return null !== $this->deletedAt;
    }

    public function updateDates(\DateTimeImmutable $start, \DateTimeImmutable $end): void
    {
        $this->startDate = $start;
        $this->endDate = $end;
        $this->touch();
        // Optionally: $this->recordEvent(new WorkEntryUpdated(...));
    }

    public function delete(): void
    {
        $this->deletedAt = new \DateTimeImmutable();
        $this->touch();
        // $this->recordEvent(new WorkEntryDeleted(...));
    }

    public function restore(): void
    {
        $this->deletedAt = null;
        $this->touch();
    }

    private function touch(): void
    {
        $this->updatedAt = new \DateTimeImmutable();
    }
}
