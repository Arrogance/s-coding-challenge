<?php

declare(strict_types=1);

namespace App\Tests\Common\Infrastructure\EventBus;

use App\Common\Application\Subscriber\Subscriber;
use App\Common\Domain\Event\Event;
use App\Common\Infrastructure\EventBus\EventBus;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Messenger\Envelope;
use Symfony\Component\Messenger\MessageBusInterface;

class DummyEvent extends Event
{
}

class DummySubscriber extends Subscriber
{
    public bool $handled = false;

    public function handle(Event $event): void
    {
        $this->handled = true;
    }

    public static function getSubscribedEvents(): array
    {
        return [DummyEvent::class => 10];
    }
}

class EventBusTest extends TestCase
{
    public function testItDispatchesEventToSubscriber(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $eventBus = new EventBus($messageBus);

        $subscriber = new DummySubscriber();
        $eventBus->registerSubscribers($subscriber);

        $event = new DummyEvent();
        $eventBus->dispatch($event);

        $this->assertTrue($subscriber->handled);
    }

    public function testItDispatchesAsyncEventViaMessenger(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);

        $messageBus->expects($this->once())
                   ->method('dispatch')
                   ->with($this->isInstanceOf(DummyEvent::class))
                   ->willReturn(new Envelope(new \stdClass()));

        $eventBus = new EventBus($messageBus);

        $subscriber = new DummySubscriber();
        $eventBus->registerSubscribers($subscriber);

        $event = new DummyEvent();
        $event->markAsAsynchronous();

        $eventBus->dispatch($event);
    }

    public function testItStopsDispatchingWhenEventIsStopped(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $eventBus = new EventBus($messageBus);

        $subscriber1 = new class extends DummySubscriber {
            public function handle(Event $event): void
            {
                parent::handle($event);
                $event->stop();
            }
        };

        $subscriber2 = new DummySubscriber();

        $eventBus->registerSubscribers($subscriber1, $subscriber2);

        $event = new DummyEvent();
        $eventBus->dispatch($event);

        $this->assertTrue($subscriber1->handled);
        $this->assertFalse($subscriber2->handled);
    }

    public function testItSkipsAlreadyPassedSubscriber(): void
    {
        $messageBus = $this->createMock(MessageBusInterface::class);
        $eventBus = new EventBus($messageBus);

        $subscriber = new DummySubscriber();

        $eventBus->registerSubscribers($subscriber);

        $event = new DummyEvent();
        $event->markSubscriberAsPassed($subscriber::class);

        $eventBus->dispatch($event);

        $this->assertFalse($subscriber->handled);
    }
}
