<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dummy')]
class DummyController
{
    public function __invoke(CommandBusInterface $commandBus): JsonResponse
    {
        return new JsonResponse(['foo' => 'potato']);
    }
}
