<?php

declare(strict_types=1);

namespace App\Tests\WorkEntry\Application\Subscriber;

use App\Common\Application\CommandBus\CommandBusInterface;
use App\Common\Domain\Event\UserDeletedEvent;
use App\WorkEntry\Application\Command\DeleteWorkEntryByUserCommand;
use App\WorkEntry\Application\Subscriber\UserDeletedEventSubscriber;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class UserDeletedEventSubscriberTest extends TestCase
{
    private CommandBusInterface&MockObject $commandBus;

    private LoggerInterface&MockObject $logger;

    private UserDeletedEventSubscriber $subscriber;

    protected function setUp(): void
    {
        $this->commandBus = $this->createMock(CommandBusInterface::class);
        $this->logger = $this->createMock(LoggerInterface::class);

        $this->subscriber = new UserDeletedEventSubscriber(
            commandBus: $this->commandBus,
            logger: $this->logger
        );
    }

    public function testItHandlesUserDeletedEvent(): void
    {
        $userId = '123e4567-e89b-12d3-a456-426614174000';
        $event = new UserDeletedEvent($userId, (new \DateTimeImmutable())->format(\DateTimeInterface::ATOM));

        $this->logger
            ->expects($this->once())
            ->method('info')
            ->with(
                'UserDeletedEvent event received: {event}',
                ['event' => $event]
            );

        $this->commandBus
            ->expects($this->once())
            ->method('send')
            ->with($this->callback(
                fn ($command) => $command instanceof DeleteWorkEntryByUserCommand
                && $command->userId === $userId
            ));

        $this->subscriber->handle($event);
    }

    public function testItReturnsSubscribedEvents(): void
    {
        $events = UserDeletedEventSubscriber::getSubscribedEvents();
        $this->assertArrayHasKey(UserDeletedEvent::class, $events);
        $this->assertEquals(10, $events[UserDeletedEvent::class]);
    }
}
