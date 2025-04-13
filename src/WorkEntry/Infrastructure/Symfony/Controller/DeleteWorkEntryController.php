<?php

declare(strict_types=1);

namespace App\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Attribute\RequireAuth;
use App\WorkEntry\Application\Command\DeleteWorkEntryCommand;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work-entries/{id}', name: 'work_entry_delete', methods: ['DELETE'])]
#[RequireAuth]
readonly class DeleteWorkEntryController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $userId = $request->attributes->get('userId');

        $command = new DeleteWorkEntryCommand($id, $userId);

        $this->commandBus->send($command);

        return new JsonResponse(
            204
        );
    }
}
