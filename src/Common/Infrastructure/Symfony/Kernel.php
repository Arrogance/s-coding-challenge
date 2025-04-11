<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony;

use App\Common\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterCommandMiddlewaresPass;
use App\Common\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterHandlersOnCommandBusPass;
use App\Common\Infrastructure\Symfony\DependencyInjection\Compiler\RegisterSubscribersOnEventBusPass;
use Symfony\Bundle\FrameworkBundle\Kernel\MicroKernelTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    use MicroKernelTrait;

    protected function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new RegisterHandlersOnCommandBusPass());
        $container->addCompilerPass(new RegisterCommandMiddlewaresPass());
        $container->addCompilerPass(new RegisterSubscribersOnEventBusPass());
    }
}
