<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\UpdateUserCommand;
use App\User\Application\Response\UserResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users/{id}', name: 'users_update', methods: ['PUT'])]
readonly class UpdateUserController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            throw new InvalidRequestException('Invalid JSON payload.');
        }

        foreach (['name', 'email'] as $field) {
            if (empty($data[$field]) || !\is_string($data[$field])) {
                throw new InvalidRequestException("Missing or invalid '$field'");
            }
        }

        $command = new UpdateUserCommand(
            id: $id,
            name: $data['name'],
            email: $data['email'],
        );

        $user = $this->commandBus->send($command);

        return new JsonResponse(
            UserResponse::fromEntity($user),
            200
        );
    }
}
