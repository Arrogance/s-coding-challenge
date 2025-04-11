<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\DependencyInjection\Compiler;

use App\Common\Application\CommandBus\CommandBusInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterCommandMiddlewaresPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(CommandBusInterface::class)) {
            return;
        }

        $definition = $container->findDefinition(CommandBusInterface::class);

        $middlewareServices = [];
        foreach ($container->findTaggedServiceIds('app.command_middleware') as $id => $tags) {
            $priority = $tags[0]['priority'] ?? 0;
            $middlewareServices[] = ['ref' => new Reference($id), 'priority' => $priority];
        }

        // Sort by priority DESC (higher = earlier)
        usort($middlewareServices, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        // Extract service references only
        $middlewareRefs = array_column($middlewareServices, 'ref');

        $definition->addMethodCall('registerMiddlewares', [...$middlewareRefs]);
    }
}
