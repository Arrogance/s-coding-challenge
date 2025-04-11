<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\DependencyInjection\Compiler;

use App\Common\Application\CommandBus\CommandBusInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterHandlersOnCommandBusPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(CommandBusInterface::class)) {
            return;
        }

        $definition = $container->findDefinition(CommandBusInterface::class);

        $handlerRefs = [];

        foreach ($container->findTaggedServiceIds('app.command_handler') as $id => $tags) {
            // Optionally support priority like middleware
            $priority = $tags[0]['priority'] ?? 0;
            $handlerRefs[] = ['ref' => new Reference($id), 'priority' => $priority];
        }

        // Optional: sort by priority if you want to support ordered handlers (e.g., decorator-like)
        usort($handlerRefs, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        $definition->addMethodCall('registerHandlers', [...array_column($handlerRefs, 'ref')]);
    }
}
