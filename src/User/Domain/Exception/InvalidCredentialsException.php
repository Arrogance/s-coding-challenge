<?php

declare(strict_types=1);

namespace App\User\Domain\Exception;

use App\Common\Domain\Exception\DomainException;

final class InvalidCredentialsException extends DomainException
{
    public function __construct(
        ?string $value = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = 'Provided credentials does not match the user.';
        if (null !== $value) {
            $message .= ": $value";
        }

        parent::__construct($message, $code, $previous);
    }

    public function statusCode(): int
    {
        return 401;
    }
}
