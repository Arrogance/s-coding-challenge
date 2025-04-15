<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Application\Handler;

use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\ListWorkEntriesCommand;
use App\WorkEntry\Application\Handler\ListWorkEntriesHandler;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class ListWorkEntriesHandlerTest extends TestCase
{
    private Generator $faker;

    private WorkEntryRepositoryInterface&MockObject $repository;

    private ListWorkEntriesHandler $handler;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->repository = $this->createMock(WorkEntryRepositoryInterface::class);
        $this->handler = new ListWorkEntriesHandler($this->repository);
    }

    public function testItReturnsPaginatedWorkEntries(): void
    {
        $userId = $this->faker->uuid();
        $offset = 0;
        $limit = 10;

        $command = new ListWorkEntriesCommand($userId, $offset, $limit);

        $entries = [
            $this->createMock(WorkEntry::class),
            $this->createMock(WorkEntry::class),
        ];

        $this->repository
            ->expects($this->once())
            ->method('findPaginated')
            ->with(
                new UserId($userId),
                $offset,
                $limit
            )
            ->willReturn($entries);

        $result = $this->handler->handle($command);

        $this->assertSame($entries, $result);
    }
}
