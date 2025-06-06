<?php

declare(strict_types=1);

namespace App\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Attribute\RequireAuth;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\WorkEntry\Application\Command\UpdateWorkEntryCommand;
use App\WorkEntry\Application\Response\WorkEntryResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work-entries/{id}', name: 'work_entry_update', methods: ['PUT'])]
#[RequireAuth]
readonly class UpdateWorkEntryController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request, string $id): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $request->attributes->get('userId');

        if (!\is_array($data)) {
            throw new InvalidRequestException('Invalid JSON payload.');
        }

        foreach (['start_date', 'end_date'] as $field) {
            if (empty($data[$field]) || !\is_string($data[$field])) {
                throw new InvalidRequestException("Missing or invalid '$field'");
            }
        }

        try {
            $start = new \DateTimeImmutable($data['start_date']);
            $end = new \DateTimeImmutable($data['end_date']);
        } catch (\DateMalformedStringException $e) {
            throw new InvalidRequestException("Invalid 'start_date' and 'end_date' provided.", previous: $e);
        }

        if ($start >= $end) {
            throw new InvalidRequestException('Start date must be before end date.');
        }

        $command = new UpdateWorkEntryCommand(
            id: $id,
            userId: $userId,
            startDate: $start,
            endDate: $end,
        );

        $workEntry = $this->commandBus->send($command);

        return new JsonResponse(
            WorkEntryResponse::fromEntity($workEntry),
            200
        );
    }
}
