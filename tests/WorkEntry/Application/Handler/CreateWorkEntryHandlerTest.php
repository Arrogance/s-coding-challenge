<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\Event\Event;
use App\WorkEntry\Application\Command\CreateWorkEntryCommand;
use App\WorkEntry\Application\Handler\CreateWorkEntryHandler;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class CreateWorkEntryHandlerTest extends TestCase
{
    private Generator $faker;

    private WorkEntryRepositoryInterface&MockObject $repository;

    private EventBusInterface&MockObject $eventBus;

    private CreateWorkEntryHandler $handler;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->repository = $this->createMock(WorkEntryRepositoryInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new CreateWorkEntryHandler(
            workEntryRepository: $this->repository,
            eventBus: $this->eventBus
        );
    }

    public function testItCreatesAndPersistsWorkEntry(): void
    {
        $userId = $this->faker->uuid();
        $startDate = new \DateTimeImmutable('-1 hour');
        $endDate = new \DateTimeImmutable();

        $command = new CreateWorkEntryCommand(
            userId: $userId,
            startDate: $startDate,
            endDate: $endDate
        );

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($this->callback(fn (WorkEntry $entry) => $entry->userId()->value() === $userId
                    && $entry->startDate() === $startDate
                    && $entry->endDate() === $endDate));

        $this->eventBus
            ->expects($this->exactly(1))
            ->method('dispatch')
            ->with($this->isInstanceOf(Event::class));

        $result = $this->handler->handle($command);

        $this->assertInstanceOf(WorkEntry::class, $result);
        $this->assertEquals($userId, $result->userId()->value());
        $this->assertEquals($startDate, $result->startDate());
        $this->assertEquals($endDate, $result->endDate());
    }
}
