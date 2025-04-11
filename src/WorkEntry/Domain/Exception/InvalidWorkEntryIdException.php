<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Exception;

class InvalidWorkEntryIdException extends \DomainException
{
    public function __construct(
        ?string $value = null,
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        $message = 'Provided WorkEntry Id is not a valid UUID';
        if (null !== $value) {
            $message .= ": $value";
        }

        parent::__construct($message, $code, $previous);
    }
}
