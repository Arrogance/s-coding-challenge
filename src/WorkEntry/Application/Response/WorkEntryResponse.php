<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Response;

use App\Common\Application\Response\Response;
use App\User\Domain\Entity\User;
use App\WorkEntry\Domain\Entity\WorkEntry;

final class WorkEntryResponse extends Response
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly string $startDate,
        public readonly string $endDate,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }

    public static function fromEntity(object $entity): self
    {
        /* @var WorkEntry $entity */
        return new self(
            id: $entity->id()->value(),
            userId: $entity->userId()->value(),
            startDate: $entity->startDate()->format(\DATE_ATOM),
            endDate:  $entity->endDate()->format(\DATE_ATOM),
            createdAt: $entity->createdAt()->format(\DATE_ATOM),
            updatedAt: $entity->updatedAt()->format(\DATE_ATOM)
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'userId' => $this->userId,
            'start_date' => $this->startDate,
            'end_date' => $this->endDate,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
