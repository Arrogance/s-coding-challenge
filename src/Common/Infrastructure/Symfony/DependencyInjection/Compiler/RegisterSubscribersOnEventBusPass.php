<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\DependencyInjection\Compiler;

use App\Common\Application\EventBus\EventBusInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

class RegisterSubscribersOnEventBusPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        if (!$container->has(EventBusInterface::class)) {
            return;
        }

        $definition = $container->findDefinition(EventBusInterface::class);

        $subscriberRefs = [];

        foreach ($container->findTaggedServiceIds('app.event_subscriber') as $id => $tags) {
            $priority = $tags[0]['priority'] ?? 0;
            $subscriberRefs[] = ['ref' => new Reference($id), 'priority' => $priority];
        }

        usort($subscriberRefs, fn ($a, $b) => $b['priority'] <=> $a['priority']);

        $definition->addMethodCall('registerSubscribers', [...array_column($subscriberRefs, 'ref')]);
    }
}
