<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\CommandBus;

use App\Common\Application\Command\Command;
use App\Common\Application\CommandBus\Exception\NoHandlerFoundForCurrentCommandException;
use App\Common\Application\CommandBus\Middleware\MiddlewareInterface;
use App\Common\Application\Handler\Handler;

class CommandBus implements \App\Common\Application\CommandBus\CommandBusInterface
{
    /**
     * @var array<Handler>
     */
    private array $handlers = [];

    /** @var array<MiddlewareInterface> */
    private array $middlewares = [];

    public function send(Command $command): mixed
    {
        $handler = $this->handlers[$command::class] ??
            throw new NoHandlerFoundForCurrentCommandException($command::class)
        ;

        $coreHandler = fn (Command $command) => $handler->handle($command);

        // Wrap the core handler in all middlewares
        $pipeline = array_reduce(
            array_reverse($this->middlewares),
            fn ($next, MiddlewareInterface $middleware) => fn (Command $command) => $middleware->handle($command, $next),
            $coreHandler
        );

        return $pipeline($command);
    }

    public function registerHandlers(Handler ...$handlers): void
    {
        foreach ($handlers as $handler) {
            $this->handlers[$handler::getHandledCommand()] = $handler;
        }
    }

    public function registerMiddlewares(MiddlewareInterface ...$middlewares): void
    {
        $this->middlewares = $middlewares;
    }
}
