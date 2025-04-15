<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\WorkEntry\Application\Command\DeleteWorkEntryCommand;
use App\WorkEntry\Infrastructure\Symfony\Controller\DeleteWorkEntryController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class DeleteWorkEntryControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private DeleteWorkEntryController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new DeleteWorkEntryController($this->commandBus);
    }

    public function testItDeletesAWorkEntry(): void
    {
        $entryId = $this->faker->uuid();
        $userId = $this->faker->uuid();

        $request = new Request();
        $request->attributes->set('userId', $userId);

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (DeleteWorkEntryCommand $cmd) => $cmd->id === $entryId
                && $cmd->userId === $userId
            ));

        $response = $this->controller->__invoke($request, $entryId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
