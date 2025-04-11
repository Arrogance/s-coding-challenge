<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Exception;

use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

abstract class BaseHttpException extends \RuntimeException implements HttpExceptionInterface
{
    protected array $headers;

    public function __construct(
        string $message = '',
        protected int $statusCode = 500,
        array $headers = [],
        int $code = 0,
        ?\Throwable $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
