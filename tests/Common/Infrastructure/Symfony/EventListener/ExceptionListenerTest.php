<?php

declare(strict_types=1);

namespace App\Tests\Common\Infrastructure\Symfony\EventListener;

use App\Common\Infrastructure\Symfony\EventListener\ExceptionListener;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;

class ExceptionListenerTest extends TestCase
{
    private function createEvent(\Throwable $exception): ExceptionEvent
    {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $request = new Request();

        return new ExceptionEvent($kernel, $request, HttpKernelInterface::MAIN_REQUEST, $exception);
    }

    public function testItReturnsGenericResponseForInternalException(): void
    {
        $_ENV['APP_ENV'] = 'prod';

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            'error',
            'Something went wrong',
            $this->arrayHasKey('exception')
        );

        $listener = new ExceptionListener($logger);

        $event = $this->createEvent(new \RuntimeException('Something went wrong'));

        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertEquals(500, $response->getStatusCode());
        $this->assertStringContainsString('Internal server error', $response->getContent());
    }

    public function testItReturnsCustomStatusCodeForHttpExceptions(): void
    {
        $_ENV['APP_ENV'] = 'prod';

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())->method('log')->with(
            'warning',
            'Resource not found',
            $this->arrayHasKey('exception')
        );

        $listener = new ExceptionListener($logger);
        $event = $this->createEvent(new NotFoundHttpException('Resource not found'));

        $listener->onKernelException($event);

        $response = $event->getResponse();
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertStringContainsString('Resource not found', $response->getContent());
    }

    public function testItAppendsDebugInfoInNonProdEnv(): void
    {
        $_ENV['APP_ENV'] = 'dev';

        $logger = $this->createMock(LoggerInterface::class);
        $listener = new ExceptionListener($logger);

        $event = $this->createEvent(new \RuntimeException('Fail'));

        $listener->onKernelException($event);

        $response = json_decode($event->getResponse()->getContent(), true);

        $this->assertArrayHasKey('exception', $response);
        $this->assertArrayHasKey('trace', $response);
    }
}
