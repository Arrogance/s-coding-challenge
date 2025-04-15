<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\WorkEntry\Application\Command\PatchWorkEntryCommand;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use App\WorkEntry\Infrastructure\Symfony\Controller\PartialUpdateWorkEntryController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class PartialUpdateWorkEntryControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private PartialUpdateWorkEntryController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new PartialUpdateWorkEntryController($this->commandBus);
    }

    public function testItUpdatesWorkEntrySuccessfully(): void
    {
        $entryId = $this->faker->uuid();
        $userId = $this->faker->uuid();
        $start = new \DateTimeImmutable('-2 hours');
        $end = new \DateTimeImmutable('-1 hour');

        $request = new Request(content: json_encode([
            'start_date' => $start->format(\DateTimeInterface::ATOM),
            'end_date' => $end->format(\DateTimeInterface::ATOM),
        ]));
        $request->attributes->set('userId', $userId);

        $entry = new WorkEntry(
            new WorkEntryId($entryId),
            new UserId($userId),
            $start,
            $end
        );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (PatchWorkEntryCommand $cmd) => $cmd->id === $entryId
                && $cmd->userId === $userId
                && $cmd->startDate?->format(\DateTimeInterface::ATOM) === $start->format(\DateTimeInterface::ATOM)
                && $cmd->endDate?->format(\DateTimeInterface::ATOM) === $end->format(\DateTimeInterface::ATOM)
            ))
            ->willReturn($entry);

        $response = $this->controller->__invoke($request, $entryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($userId, $data['user_id']);
        $this->assertEquals($entryId, $data['id']);
    }

    public function testItThrowsOnInvalidJson(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid JSON payload.');

        $request = new Request(content: 'not-json');
        $request->attributes->set('userId', $this->faker->uuid());

        $this->controller->__invoke($request, $this->faker->uuid());
    }

    public function testItThrowsWhenNoFieldsProvided(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("At least one field must be provided: 'start_date', 'end_date'");

        $request = new Request(content: json_encode([]));
        $request->attributes->set('userId', $this->faker->uuid());

        $this->controller->__invoke($request, $this->faker->uuid());
    }

    public function testItThrowsOnMalformedDates(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("Invalid 'start_date' and 'end_date' provided.");

        $request = new Request(content: json_encode([
            'start_date' => 'bad',
            'end_date' => 'worse',
        ]));
        $request->attributes->set('userId', $this->faker->uuid());

        $this->controller->__invoke($request, $this->faker->uuid());
    }

    public function testItThrowsIfStartDateIsAfterEndDate(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Start date must be before end date.');

        $request = new Request(content: json_encode([
            'start_date' => (new \DateTimeImmutable('+1 hour'))->format(\DateTimeInterface::ATOM),
            'end_date' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]));
        $request->attributes->set('userId', $this->faker->uuid());

        $this->controller->__invoke($request, $this->faker->uuid());
    }
}
