<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\CreateUserCommand;
use App\User\Application\Response\UserResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users', name: 'users_create', methods: ['POST'])]
readonly class CreateUserController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!\is_array($data)) {
            throw new InvalidRequestException('Invalid JSON payload.');
        }

        foreach (['name', 'email', 'password'] as $field) {
            if (empty($data[$field]) || !\is_string($data[$field])) {
                throw new InvalidRequestException("Missing or invalid '$field'");
            }
        }

        $command = new CreateUserCommand(
            name: $data['name'],
            email: $data['email'],
            password: $data['password']
        );

        $user = $this->commandBus->send($command);

        return new JsonResponse(
            UserResponse::fromEntity($user),
            201
        );
    }
}
