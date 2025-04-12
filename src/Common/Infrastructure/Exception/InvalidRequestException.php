<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Exception;

final class InvalidRequestException extends BaseHttpException
{
    public function __construct(string $message = 'Invalid request', ?\Throwable $previous = null)
    {
        parent::__construct($message, 400, [], 0, $previous);
    }
}
