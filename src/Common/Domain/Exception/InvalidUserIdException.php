<?php

declare(strict_types=1);

namespace App\Common\Domain\Exception;

class InvalidUserIdException extends DomainException
{
    public function __construct(
        ?string $value = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = 'Provided User Id is not a valid UUID';
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
