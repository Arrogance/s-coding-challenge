<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Common\Domain\Exception\DomainException;

final class InvalidEmailException extends DomainException
{
    public function __construct(
        ?string $value = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = 'Provided Email is not a valid email address';
        if (null !== $value) {
            $message .= ": $value";
        }

        parent::__construct($message, $code, $previous);
    }

    public function statusCode(): int
    {
        return 400;
    }
}
