<?php

declare(strict_types=1);

namespace App\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Attribute\RequireAuth;
use App\WorkEntry\Application\Command\GetWorkEntryCommand;
use App\WorkEntry\Application\Response\WorkEntryResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work-entries/{id}', name: 'work_entry_get', methods: ['GET'])]
#[RequireAuth]
readonly class GetWorkEntryController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $userId = $request->attributes->get('userId');

        $command = new GetWorkEntryCommand($id, $userId);

        $workEntry = $this->commandBus->send($command);

        return new JsonResponse(
            WorkEntryResponse::fromEntity($workEntry),
            200
        );
    }
}
