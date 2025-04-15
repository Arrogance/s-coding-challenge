<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\ListWorkEntriesCommand;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use App\WorkEntry\Infrastructure\Symfony\Controller\ListWorkEntriesController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ListWorkEntriesControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private ListWorkEntriesController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new ListWorkEntriesController($this->commandBus);
    }

    public function testItReturnsPaginatedWorkEntries(): void
    {
        $userId = $this->faker->uuid();
        $offset = 0;
        $limit = 2;

        $start = new \DateTimeImmutable('-2 hours');
        $end = new \DateTimeImmutable('-1 hour');

        $workEntry1 = new WorkEntry(
            WorkEntryId::generate(),
            new UserId($userId),
            $start,
            $end
        );

        $workEntry2 = new WorkEntry(
            WorkEntryId::generate(),
            new UserId($userId),
            $start,
            $end
        );

        $entries = [$workEntry1, $workEntry2];

        $request = new Request([
            'offset' => $offset,
            'limit' => $limit,
        ]);
        $request->attributes->set('userId', $userId);

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (ListWorkEntriesCommand $cmd) => $cmd->userId === $userId
                && $cmd->offset === $offset
                && $cmd->limit === $limit
            ))
            ->willReturn($entries);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('meta', $data);
        $this->assertEquals($offset, $data['meta']['offset']);
        $this->assertEquals($limit, $data['meta']['limit']);
        $this->assertCount(2, $data['data']);
        $this->assertEquals($userId, $data['data'][0]['user_id']);
    }
}
