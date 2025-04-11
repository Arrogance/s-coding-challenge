<?php

declare(strict_types=1);

namespace App\User\Domain\ValueObject;

use App\User\Domain\Exception\InvalidEmailException;

final readonly class Email
{
    public function __construct(
        private string $value
    ) {
        if (!filter_var($value, \FILTER_VALIDATE_EMAIL)) {
            throw new InvalidEmailException($this->value);
        }
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value();
    }
}
