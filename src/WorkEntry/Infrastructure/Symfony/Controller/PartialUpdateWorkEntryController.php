<?php

declare(strict_types=1);

namespace App\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Attribute\RequireAuth;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\WorkEntry\Application\Command\PatchWorkEntryCommand;
use App\WorkEntry\Application\Response\WorkEntryResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/work-entries/{id}', name: 'work_entry_patch', methods: ['PATCH'])]
#[RequireAuth]
readonly class PartialUpdateWorkEntryController
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

        $hasValidField = false;
        foreach (['start_date', 'end_date'] as $field) {
            if (\array_key_exists($field, $data)) {
                $hasValidField = true;
            }
        }

        if (!$hasValidField) {
            throw new InvalidRequestException("At least one field must be provided: 'start_date', 'end_date'");
        }

        try {
            $start = isset($data['start_date']) ? new \DateTimeImmutable($data['start_date']) : null;
            $end = isset($data['end_date']) ? new \DateTimeImmutable($data['end_date']) : null;
        } catch (\DateMalformedStringException $e) {
            throw new InvalidRequestException("Invalid 'start_date' and 'end_date' provided.", previous: $e);
        }

        if ($start >= $end) {
            throw new InvalidRequestException('Start date must be before end date.');
        }

        $command = new PatchWorkEntryCommand(
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
