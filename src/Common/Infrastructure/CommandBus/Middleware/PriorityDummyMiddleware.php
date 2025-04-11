<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\CommandBus\Middleware;

use App\Common\Application\Command\Command;
use Symfony\Component\HttpFoundation\RequestStack;

readonly class PriorityDummyMiddleware implements \App\Common\Application\CommandBus\Middleware\MiddlewareInterface
{
    public function __construct(private RequestStack $requestStack)
    {
    }

    public function handle(Command $command, callable $next): mixed
    {
        $request = $this->requestStack->getCurrentRequest();
        if (!$request->headers->has('X-Api')) {
            throw new \LogicException('Request header X-Api not set');
        }

        return $next($command);
    }
}
