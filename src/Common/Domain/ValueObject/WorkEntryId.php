<?php

declare(strict_types=1);

namespace App\Common\Domain\ValueObject;

use App\Common\Domain\Exception\InvalidIdException;
use Symfony\Component\Uid\Uuid;

final readonly class WorkEntryId
{
    public function __construct(
        private string $uuid
    ) {
        if (!Uuid::isValid($this->uuid)) {
            throw new InvalidIdException($this->uuid);
        }
    }

    public static function generate(): self
    {
        return new self(Uuid::v7()->toRfc4122());
    }

    public function value(): string
    {
        return $this->uuid;
    }

    public function __toString(): string
    {
        return $this->uuid;
    }
}
