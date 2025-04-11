<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\PatchUserCommand;
use App\User\Application\Response\UserResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users/{id}', name: 'users_patch', methods: ['PATCH'])]
readonly class PartialUpdateUserController
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

        $hasValidField = false;
        foreach (['name', 'email'] as $field) {
            if (\array_key_exists($field, $data)) {
                $hasValidField = true;
            }
        }

        if (!$hasValidField) {
            throw new InvalidRequestException("At least one field must be provided: 'name', 'email'");
        }

        $command = new PatchUserCommand(
            id: $id,
            name: $data['name'] ?? null,
            email: $data['email'] ?? null
        );

        $user = $this->commandBus->send($command);

        return new JsonResponse(
            UserResponse::fromEntity($user),
            200
        );
    }
}
