<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Application\Handler;

use App\Common\Application\EventBus\EventBusInterface;
use App\Common\Domain\Event\Event;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\DeleteWorkEntryByUserCommand;
use App\WorkEntry\Application\Handler\DeleteWorkEntryByUserHandler;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class DeleteWorkEntryByUserHandlerTest extends TestCase
{
    private Generator $faker;

    private WorkEntryRepositoryInterface&MockObject $repository;

    private EventBusInterface&MockObject $eventBus;

    private DeleteWorkEntryByUserHandler $handler;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->repository = $this->createMock(WorkEntryRepositoryInterface::class);
        $this->eventBus = $this->createMock(EventBusInterface::class);

        $this->handler = new DeleteWorkEntryByUserHandler(
            workEntryRepository: $this->repository,
            eventBus: $this->eventBus
        );
    }

    public function testItDeletesWorkEntriesAndDispatchesEvents(): void
    {
        $userId = $this->faker->uuid();
        $command = new DeleteWorkEntryByUserCommand($userId);

        $mockEvent = $this->createMock(Event::class);

        $entries = [];
        for ($i = 0; $i < 3; ++$i) {
            $entry = $this->createMock(WorkEntry::class);
            $entry->expects($this->once())->method('delete');
            $entry->expects($this->once())->method('pullDomainEvents')->willReturn([$mockEvent]);
            $entries[] = $entry;
        }

        $this->repository
            ->expects($this->once())
            ->method('iterateActiveByUser')
            ->with(new UserId($userId), 100)
            ->willReturn($this->generatorFrom($entries));

        $this->repository->expects($this->once())->method('beginTransaction');
        $this->repository->expects($this->once())->method('commit');
        $this->repository->expects($this->any())->method('rollback');

        $this->repository
            ->expects($this->exactly(4))
            ->method('save')
            ->willReturnCallback(function ($entry, bool $flush = false) use (&$entries): void {
                static $call = 0;

                if ($call < 3) {
                    TestCase::assertSame($entries[$call], $entry);
                    TestCase::assertFalse($flush);
                }

                if (3 === $call) {
                    TestCase::assertSame($entries[2], $entry);
                }

                ++$call;
            });

        $this->eventBus
            ->expects($this->exactly(3))
            ->method('dispatch')
            ->with($mockEvent);

        $this->handler->handle($command);
    }

    public function testItRollsBackTransactionOnError(): void
    {
        $userId = $this->faker->uuid();
        $command = new DeleteWorkEntryByUserCommand($userId);

        $entry = $this->createMock(WorkEntry::class);
        $entry->expects($this->once())->method('delete');
        $entry->expects($this->never())->method('pullDomainEvents');

        $this->repository
            ->expects($this->once())
            ->method('iterateActiveByUser')
            ->willReturn($this->generatorFrom([$entry]));

        $this->repository->expects($this->once())->method('beginTransaction');
        $this->repository->expects($this->once())->method('rollback');
        $this->repository->expects($this->never())->method('commit');

        $this->repository
            ->expects($this->once())
            ->method('save')
            ->willThrowException(new \RuntimeException('DB Error'));

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('DB Error');

        $this->handler->handle($command);
    }

    private function generatorFrom(array $items): \Generator
    {
        foreach ($items as $item) {
            yield $item;
        }
    }
}
