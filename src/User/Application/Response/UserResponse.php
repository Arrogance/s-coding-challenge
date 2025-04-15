<?php

declare(strict_types=1);

namespace App\User\Application\Response;

use App\Common\Application\Response\Response;
use App\User\Domain\Entity\User;

final class UserResponse extends Response
{
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $email,
        public readonly string $createdAt,
        public readonly string $updatedAt
    ) {
    }

    public static function fromEntity(object $entity): self
    {
        /* @var User $entity */
        return new self(
            id: $entity->id()->value(),
            name: $entity->name(),
            email: (string) $entity->email(),
            createdAt: $entity->createdAt()->format(\DATE_ATOM),
            updatedAt: $entity->updatedAt()->format(\DATE_ATOM)
        );
    }

    public function jsonSerialize(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'created_at' => $this->createdAt,
            'updated_at' => $this->updatedAt,
        ];
    }
}
