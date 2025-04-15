<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Application\Command\GetWorkEntryCommand;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use App\WorkEntry\Infrastructure\Symfony\Controller\GetWorkEntryController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class GetWorkEntryControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private GetWorkEntryController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new GetWorkEntryController($this->commandBus);
    }

    public function testItReturnsAWorkEntry(): void
    {
        $entryId = $this->faker->uuid();
        $userId = $this->faker->uuid();

        $request = new Request();
        $request->attributes->set('userId', $userId);

        $start = new \DateTimeImmutable('-1 hour');
        $end = new \DateTimeImmutable();

        $workEntry = new WorkEntry(
            new WorkEntryId($entryId),
            new UserId($userId),
            $start,
            $end
        );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (GetWorkEntryCommand $cmd) => $cmd->id === $entryId && $cmd->userId === $userId
            ))
            ->willReturn($workEntry);

        $response = $this->controller->__invoke($request, $entryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);
        $this->assertEquals($entryId, $data['id']);
        $this->assertEquals($userId, $data['user_id']);
        $this->assertEquals($start->format(\DateTimeInterface::ATOM), $data['start_date']);
        $this->assertEquals($end->format(\DateTimeInterface::ATOM), $data['end_date']);
    }
}
