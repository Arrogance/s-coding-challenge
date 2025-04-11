<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Exception;

final class ConflictException extends BaseHttpException
{
    public function __construct(string $message = 'Conflict.', ?\Throwable $previous = null)
    {
        parent::__construct($message, 409, [], 0, $previous);
    }
}
