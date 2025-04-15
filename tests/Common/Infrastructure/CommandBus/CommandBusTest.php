<?php

declare(strict_types=1);

namespace App\Tests\Common\Infrastructure\CommandBus;

use App\Common\Application\Command\Command;
use App\Common\Application\CommandBus\Exception\NoHandlerFoundForCurrentCommandException;
use App\Common\Application\CommandBus\Middleware\MiddlewareInterface;
use App\Common\Application\Handler\Handler;
use App\Common\Infrastructure\CommandBus\CommandBus;
use PHPUnit\Framework\TestCase;

class DummyCommand extends Command
{
}

class DummyHandler extends Handler
{
    public function handle(Command|DummyCommand $command): string
    {
        return 'handled';
    }

    public static function getHandledCommand(): string
    {
        return DummyCommand::class;
    }
}

class CountingMiddleware implements MiddlewareInterface
{
    public int $count = 0;

    public function handle(Command $command, callable $next): mixed
    {
        ++$this->count;

        return $next($command);
    }
}

class CommandBusTest extends TestCase
{
    public function testItExecutesHandlerIfRegistered(): void
    {
        $bus = new CommandBus();
        $bus->registerHandlers(new DummyHandler());

        $result = $bus->send(new DummyCommand());

        $this->assertSame('handled', $result);
    }

    public function testItThrowsIfHandlerNotFound(): void
    {
        $this->expectException(NoHandlerFoundForCurrentCommandException::class);

        $bus = new CommandBus();
        $bus->send(new DummyCommand());
    }

    public function testItProcessesMiddlewaresInOrder(): void
    {
        $middleware1 = new CountingMiddleware();
        $middleware2 = new CountingMiddleware();

        $bus = new CommandBus();
        $bus->registerHandlers(new DummyHandler());
        $bus->registerMiddlewares($middleware1, $middleware2);

        $bus->send(new DummyCommand());

        $this->assertSame(1, $middleware1->count);
        $this->assertSame(1, $middleware2->count);
    }
}
