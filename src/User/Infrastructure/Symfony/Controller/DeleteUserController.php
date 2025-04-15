<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\User\Application\Command\DeleteUserCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users/{id}', name: 'users_delete', methods: ['DELETE'])]
readonly class DeleteUserController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new DeleteUserCommand($id);

        $this->commandBus->send($command);

        return new JsonResponse(null, 204);
    }
}
