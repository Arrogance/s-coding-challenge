<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\User\Application\Command\DeleteUserCommand;
use App\User\Infrastructure\Symfony\Controller\DeleteUserController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

final class DeleteUserControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private DeleteUserController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new DeleteUserController($this->commandBus);
    }

    public function testItDeletesUserSuccessfully(): void
    {
        $userId = $this->faker->uuid();

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(fn (DeleteUserCommand $command) => $command->id === $userId));

        $response = $this->controller->__invoke($userId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }
}
