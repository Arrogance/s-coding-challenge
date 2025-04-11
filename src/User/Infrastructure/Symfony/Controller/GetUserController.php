<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\User\Application\Command\GetUserCommand;
use App\User\Application\Response\UserResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users/{id}', name: 'users_get', methods: ['GET'])]
readonly class GetUserController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(string $id): JsonResponse
    {
        $command = new GetUserCommand($id);

        $user = $this->commandBus->send($command);

        return new JsonResponse(
            UserResponse::fromEntity($user),
            200
        );
    }
}
