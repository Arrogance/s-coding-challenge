<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\Event\Event;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\UpdateWorkEntryCommand;
use App\WorkEntry\Application\Handler\UpdateWorkEntryHandler;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class UpdateWorkEntryHandlerTest extends TestCase
{
    private Generator $faker;

    private WorkEntryRepositoryInterface&MockObject $repository;

    private EventBusInterface&MockObject $eventBus;

    private UpdateWorkEntryHandler $handler;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->repository = $this->createMock(WorkEntryRepositoryInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new UpdateWorkEntryHandler(
            workEntryRepository: $this->repository,
            eventBus: $this->eventBus
        );
    }

    public function testItUpdatesStartAndEndDateAndDispatchesEvents(): void
    {
        $userId = $this->faker->uuid();
        $entryId = $this->faker->uuid();
        $startDate = new \DateTimeImmutable('-3 hours');
        $endDate = new \DateTimeImmutable('-1 hour');

        $command = new UpdateWorkEntryCommand(
            id: $entryId,
            userId: $userId,
            startDate: $startDate,
            endDate: $endDate
        );

        $mockEvent = $this->createMock(Event::class);

        $entry = $this->createMock(WorkEntry::class);
        $entry->expects($this->once())->method('setStartDate')->with($startDate);
        $entry->expects($this->once())->method('setEndDate')->with($endDate);
        $entry->expects($this->once())->method('pullDomainEvents')->willReturn([$mockEvent]);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(new UserId($userId), new WorkEntryId($entryId))
            ->willReturn($entry);

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->with($entry);

        $this->eventBus
            ->expects($this->once())
            ->method('dispatch')
            ->with($mockEvent);

        $result = $this->handler->handle($command);

        $this->assertSame($entry, $result);
    }
}
