<?php

declare(strict_types=1);

namespace App\Common\Application\CommandBus;

use App\Common\Application\Command\Command;
use App\Common\Application\CommandBus\Middleware\MiddlewareInterface;
use App\Common\Application\Handler\Handler;

interface CommandBusInterface
{
    public function send(Command $command): mixed;

    public function registerHandlers(Handler ...$handler): void;

    public function registerMiddlewares(MiddlewareInterface ...$handler): void;
}
