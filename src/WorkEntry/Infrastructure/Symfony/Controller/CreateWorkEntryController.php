<?php

declare(strict_types=1);

namespace App\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Attribute\RequireAuth;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\WorkEntry\Application\Command\CreateWorkEntryCommand;
use App\WorkEntry\Application\Response\WorkEntryResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;

#[Route('work-entries', name: 'work_entry_create', methods: ['POST'])]
#[RequireAuth]
readonly class CreateWorkEntryController
{
    public function __construct(private CommandBusInterface $commandBus)
    {
    }

    public function __invoke(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $userId = $request->attributes->get('userId');

        if (!\is_array($data)) {
            throw new InvalidRequestException('Invalid JSON payload.');
        }

        foreach (['start_date', 'end_date'] as $key) {
            if (empty($data[$key]) || !\is_string($data[$key])) {
                throw new InvalidRequestException("Missing or invalid '$key'");
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

        $command = new CreateWorkEntryCommand($userId, $start, $end);
        $workEntry = $this->commandBus->send($command);

        return new JsonResponse(WorkEntryResponse::fromEntity($workEntry), 201);
    }
}
