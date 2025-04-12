<?php

declare(strict_types=1);

namespace App\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Application\Response\PaginatedResponse;
use App\Common\Infrastructure\Symfony\Request\Pagination;
use App\User\Application\Command\ListUsersCommand;
use App\User\Application\Response\UserResponse;
use App\WorkEntry\Application\Command\ListWorkEntriesCommand;
use App\WorkEntry\Application\Response\WorkEntryResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work-entries/{userId}', name: 'work_entries_list', methods: ['GET'])]
readonly class ListWorkEntriesController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request, string $userId): JsonResponse
    {
        $pagination = new Pagination($request);

        $command = new ListWorkEntriesCommand(
            $userId,
            $pagination->offset,
            $pagination->limit
        );

        $workEntries = $this->commandBus->send($command);
        $workEntriesResponses = array_map(
            fn ($workEntry) => WorkEntryResponse::fromEntity($workEntry),
            $workEntries
        );

        return new JsonResponse(
            new PaginatedResponse(
                items: $workEntriesResponses,
                offset: $pagination->offset,
                limit: $pagination->limit
            )
        );
    }
}
