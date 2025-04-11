<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Application\Response\PaginatedResponse;
use App\Common\Infrastructure\Symfony\Request\Pagination;
use App\User\Application\Command\ListUsersCommand;
use App\User\Application\Response\UserResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/users', name: 'users_list', methods: ['GET'])]
readonly class ListUsersController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $pagination = new Pagination($request);

        $command = new ListUsersCommand($pagination->offset, $pagination->limit);

        $users = $this->commandBus->send($command);
        $userResponses = array_map(
            fn ($user) => UserResponse::fromEntity($user),
            $users
        );

        return new JsonResponse(
            new PaginatedResponse(
                items: $userResponses,
                offset: $pagination->offset,
                limit: $pagination->limit
            )
        );
    }
}
