<?php

declare(strict_types=1);

namespace App\Common\Application\CommandBus\Middleware;

use App\Common\Application\Command\Command;

interface MiddlewareInterface
{
    public function handle(Command $command, callable $next): mixed;
}
