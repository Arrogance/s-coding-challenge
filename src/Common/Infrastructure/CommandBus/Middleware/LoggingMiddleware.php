<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\CommandBus\Middleware;

use App\Common\Application\Command\Command;
use App\Common\Application\CommandBus\Middleware\MiddlewareInterface;
use Psr\Log\LoggerInterface;

readonly class LoggingMiddleware implements MiddlewareInterface
{
    public function __construct(private LoggerInterface $logger)
    {
    }

    public function handle(Command $command, callable $next): mixed
    {
        $this->logger->info('Processed command');

        return $next($command);
    }
}
