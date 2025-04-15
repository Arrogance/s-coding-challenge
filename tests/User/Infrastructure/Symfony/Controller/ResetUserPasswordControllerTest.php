<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Infrastructure\Exception\InvalidRequestException;
use App\User\Application\Command\ResetUserPasswordCommand;
use App\User\Infrastructure\Symfony\Controller\ResetUserPasswordController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ResetUserPasswordControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private ResetUserPasswordController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();

        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new ResetUserPasswordController($this->commandBus);
    }

    public function testItResetsUserPassword(): void
    {
        $userId = $this->faker->uuid();
        $newPassword = $this->faker->password();

        $request = new Request(content: json_encode([
            'new_password' => $newPassword,
        ]));

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (ResetUserPasswordCommand $cmd) => $cmd->id === $userId
                && $cmd->newPassword === $newPassword
            ));

        $response = $this->controller->__invoke($request, $userId);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(204, $response->getStatusCode());
    }

    public function testItThrowsExceptionWithInvalidJson(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid JSON payload.');

        $request = new Request(content: 'not json');
        $this->controller->__invoke($request, $this->faker->uuid());
    }

    public function testItThrowsExceptionWithMissingPassword(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Missing or invalid password.');

        $request = new Request(content: json_encode([]));
        $this->controller->__invoke($request, $this->faker->uuid());
    }

    public function testItThrowsExceptionWithInvalidPasswordType(): void
    {
        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Missing or invalid password.');

        $request = new Request(content: json_encode([
            'new_password' => ['not', 'a', 'string'],
        ]));

        $this->controller->__invoke($request, $this->faker->uuid());
    }
}
