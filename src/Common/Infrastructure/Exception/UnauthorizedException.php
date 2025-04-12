<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Exception;

final class UnauthorizedException extends BaseHttpException
{
    public function __construct(string $message = 'Unauthorized access or invalid credentials', ?\Throwable $previous = null)
    {
        parent::__construct($message, 403, [], 0, $previous);
    }
}
