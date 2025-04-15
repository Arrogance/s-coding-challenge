<?php

declare(strict_types=1);

namespace App\Tests\Common\Infrastructure\Symfony\EventListener;

use App\Common\Application\Security\TokenManagerInterface;
use App\Common\Infrastructure\Attribute\RequireAuth;
use App\Common\Infrastructure\Exception\UnauthorizedException;
use App\Common\Infrastructure\Symfony\EventListener\AuthRequestListener;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

#[RequireAuth]
class StubController
{
    public function __invoke(): void
    {
    }
}

class StubOpenController
{
    public function __invoke(): void
    {
    }
}

class AuthRequestListenerTest extends TestCase
{
    private function createEvent(
        Request $request,
        array $attributes = [],
        ?bool $closed = true
    ): ControllerEvent {
        $kernel = $this->createMock(HttpKernelInterface::class);
        $controller = [$closed ? new StubController() : new StubOpenController(), '__invoke'];

        $event = new ControllerEvent($kernel, $controller, $request, HttpKernelInterface::MAIN_REQUEST);

        $request->attributes->add($attributes);

        return $event;
    }

    public function testItDoesNothingIfControllerDoesNotRequireAuth(): void
    {
        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $listener = new AuthRequestListener($tokenManager);

        $request = new Request();
        $event = $this->createEvent(request: $request, closed: false);

        $listener($event);

        $this->assertFalse($request->attributes->has('userId'));
    }

    public function testItThrowsIfMissingAuthorizationHeader(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Missing or malformed Authorization header');

        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $listener = new AuthRequestListener($tokenManager);

        $request = new Request();
        $event = $this->createEvent($request, [RequireAuth::class => true]);

        $listener($event);
    }

    public function testItThrowsIfTokenIsInvalid(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Invalid or expired token');

        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->method('verify')->willThrowException(new \Exception());

        $listener = new AuthRequestListener($tokenManager);

        $request = new Request([], [], [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer invalid.token']);
        $event = $this->createEvent($request, [RequireAuth::class => true]);

        $listener($event);
    }

    public function testItThrowsIfTokenHasNoSub(): void
    {
        $this->expectException(UnauthorizedException::class);
        $this->expectExceptionMessage('Token does not contain subject (sub)');

        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->method('verify')->willReturn(['foo' => 'bar']); // no sub

        $listener = new AuthRequestListener($tokenManager);

        $request = new Request([], [], [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer valid.token']);
        $event = $this->createEvent($request, [RequireAuth::class => true]);

        $listener($event);
    }

    public function testItSetsUserIdOnRequestIfTokenIsValid(): void
    {
        $userId = '1234-user-id';

        $tokenManager = $this->createMock(TokenManagerInterface::class);
        $tokenManager->method('verify')->willReturn(['sub' => $userId]);

        $listener = new AuthRequestListener($tokenManager);

        $request = new Request([], [], [], [], [], ['HTTP_AUTHORIZATION' => 'Bearer valid.token']);
        $event = $this->createEvent($request, [RequireAuth::class => true]);

        $listener($event);

        $this->assertSame($userId, $request->attributes->get('userId'));
    }
}
