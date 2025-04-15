<?php

declare(strict_types=1);

namespace App\Tests\User\Infrastructure\Symfony\Controller;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\ValueObject\UserId;
use App\User\Application\Command\ListUsersCommand;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;
use App\User\Domain\ValueObject\Password;
use App\User\Infrastructure\Symfony\Controller\ListUsersController;
use Faker\Factory;
use Faker\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

final class ListUsersControllerTest extends TestCase
{
    private Generator $faker;

    private CommandBusInterface&MockObject $commandBus;

    private ListUsersController $controller;

    protected function setUp(): void
    {
        $this->faker = Factory::create();
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->controller = new ListUsersController($this->commandBus);
    }

    public function testItReturnsPaginatedListOfUsers(): void
    {
        $offset = 0;
        $limit = 2;

        $request = new Request(query: [
            'offset' => $offset,
            'limit' => $limit,
        ]);

        $users = [];
        for ($i = 0; $i < $limit; ++$i) {
            $users[] = new User(
                new UserId($this->faker->uuid()),
                $this->faker->name(),
                new Email($this->faker->email()),
                new Password($this->faker->password())
            );
        }

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn (ListUsersCommand $command) => $command->offset === $offset && $command->limit === $limit
            ))
            ->willReturn($users);

        $response = $this->controller->__invoke($request);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(200, $response->getStatusCode());

        $data = json_decode($response->getContent(), true);

        $this->assertArrayHasKey('meta', $data);
        $this->assertArrayHasKey('data', $data);

        $this->assertEquals($offset, $data['meta']['offset']);
        $this->assertEquals($limit, $data['meta']['limit']);
        $this->assertCount($limit, $data['data']);

        foreach ($data['data'] as $item) {
            $this->assertArrayHasKey('id', $item);
            $this->assertArrayHasKey('name', $item);
            $this->assertArrayHasKey('email', $item);
        }
    }
}
