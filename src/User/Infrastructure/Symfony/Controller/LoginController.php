<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\LoginCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/login', name: 'user_login', methods: ['POST'])]
readonly class LoginController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $contentType = $request->getContentTypeFormat();

        if ('json' === $contentType) {
            $data = json_decode($request->getContent(), true);
        } else {
            $data = $request->request->all();
        }

        if (!\is_array($data)) {
            throw new InvalidRequestException('Invalid request payload.');
        }

        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        if (!\is_string($email) || !\is_string($password)) {
            throw new InvalidRequestException('Email and password are required.');
        }

        $command = new LoginCommand($email, $password);

        $token = $this->commandBus->send($command);

        return new JsonResponse([
            'token' => $token,
        ]);
    }
}
