<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Application\Handler;

use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\GetWorkEntryCommand;
use App\WorkEntry\Application\Handler\GetWorkEntryHandler;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class GetWorkEntryHandlerTest extends TestCase
{
    private Generator $faker;

    private WorkEntryRepositoryInterface&MockObject $repository;

    private GetWorkEntryHandler $handler;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->repository = $this->createMock(WorkEntryRepositoryInterface::class);
        $this->handler = new GetWorkEntryHandler($this->repository);
    }

    public function testItReturnsTheExpectedWorkEntry(): void
    {
        $userId = $this->faker->uuid();
        $entryId = $this->faker->uuid();

        $command = new GetWorkEntryCommand($entryId, $userId);

        $entry = $this->createMock(WorkEntry::class);

        $this->repository
            ->expects($this->once())
            ->method('findById')
            ->with(
                new UserId($userId),
                new WorkEntryId($entryId)
            )
            ->willReturn($entry);

        $result = $this->handler->handle($command);

        $this->assertSame($entry, $result);
    }
}
