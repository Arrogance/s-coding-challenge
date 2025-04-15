<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\WorkEntry\Application\Command\CreateWorkEntryCommand;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use App\WorkEntry\Infrastructure\Symfony\Controller\CreateWorkEntryController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class CreateWorkEntryControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private CreateWorkEntryController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new CreateWorkEntryController($this->commandBus);
    }

    public function testItCreatesAWorkEntrySuccessfully(): void
    {
        $userId = $this->faker->uuid();
        $start = new \DateTimeImmutable('-1 hour');
        $end = new \DateTimeImmutable();

        $payload = [
            'start_date' => $start->format(\DateTimeInterface::ATOM),
            'end_date' => $end->format(\DateTimeInterface::ATOM),
        ];

        $request = new Request(content: json_encode($payload));
        $request->attributes->set('userId', $userId);

        $entry = new WorkEntry(
            WorkEntryId::generate(),
            new UserId($userId),
            $start,
            $end
        );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (CreateWorkEntryCommand $cmd) => $cmd->userId === $userId
                && $cmd->startDate->format(\DateTimeInterface::ATOM) === $start->format(\DateTimeInterface::ATOM)
                && $cmd->endDate->format(\DateTimeInterface::ATOM) === $end->format(\DateTimeInterface::ATOM)
            ))
            ->willReturn($entry);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(201, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        $this->assertEquals($userId, $data['user_id']);
    }

    public function testItThrowsExceptionOnInvalidJson(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid JSON payload.');

        $request = new Request(content: 'invalid');
        $request->attributes->set('userId', $this->faker->uuid());

        $this->controller->__invoke($request);
    }

    public function testItThrowsExceptionOnMissingStartDate(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("Missing or invalid 'start_date'");

        $request = new Request(content: json_encode([
            'end_date' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]));
        $request->attributes->set('userId', $this->faker->uuid());

        $this->controller->__invoke($request);
    }

    public function testItThrowsExceptionOnMalformedDates(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage("Invalid 'start_date' and 'end_date' provided.");

        $request = new Request(content: json_encode([
            'start_date' => 'no-date',
            'end_date' => 'also-bad',
        ]));
        $request->attributes->set('userId', $this->faker->uuid());

        $this->controller->__invoke($request);
    }

    public function testItThrowsExceptionOnStartDateAfterEndDate(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Start date must be before end date.');

        $request = new Request(content: json_encode([
            'start_date' => (new \DateTimeImmutable('+1 hour'))->format(\DateTimeInterface::ATOM),
            'end_date' => (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM),
        ]));
        $request->attributes->set('userId', $this->faker->uuid());

        $this->controller->__invoke($request);
    }
}
