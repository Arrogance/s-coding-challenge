<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

final readonly class Password
{
    public function __construct(
        private string $hash
    ) {
    }

    public function value(): string
    {
        return $this->hash;
    }

    public function equals(self $other): bool
    {
        return $this->hash === $other->hash;
    }
}
