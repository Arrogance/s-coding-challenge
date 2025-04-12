<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\ResetUserPasswordCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users/{id}/password-reset', name: 'users_password_reset', methods: ['POST'])]
readonly class ResetUserPasswordController
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

        if (empty($data['new_password']) || !\is_string($data['new_password'])) {
            throw new InvalidRequestException('Missing or invalid password.');
        }

        $command = new ResetUserPasswordCommand(
            id: $id,
            newPassword: $data['new_password']
        );

        $this->commandBus->send($command);

        return new JsonResponse(null, 204);
    }
}
