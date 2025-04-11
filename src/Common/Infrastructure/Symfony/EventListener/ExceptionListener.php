<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\EventListener;

use App\Common\Infrastructure\Exception\DomainToHttpExceptionMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

readonly class ExceptionListener
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function onKernelException(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        // Map domain exceptions if needed
        if ($mapped = DomainToHttpExceptionMapper::map($exception)) {
            $exception = $mapped;
        }

        $statusCode = 500;
        $response = ['error' => 'Internal server error'];

        if ($exception instanceof HttpExceptionInterface) {
            $statusCode = $exception->getStatusCode();
            $response['error'] = $exception->getMessage();
        }

        // Log all exceptions
        $this->logException($exception, $statusCode);

        if ('prod' !== $_ENV['APP_ENV']) {
            $response['exception'] = $exception::class;
            $response['trace'] = explode("\n", $exception->getTraceAsString());
        }

        $event->setResponse(new JsonResponse($response, $statusCode));
    }

    private function logException(\Throwable $exception, int $statusCode): void
    {
        $context = [
            'exception' => $exception,
            'status_code' => $statusCode,
        ];

        $level = match (true) {
            $statusCode >= 500 => 'error',
            $statusCode >= 400 => 'warning',
            default => 'info',
        };

        $this->logger->log($level, $exception->getMessage(), $context);
    }
}
