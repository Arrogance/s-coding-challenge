<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\EventListener;

use App\Common\Application\Security\TokenManagerInterface;
use App\Common\Infrastructure\Attribute\RequireAuth;
use App\Common\Infrastructure\Exception\UnauthorizedException;
use Symfony\Component\HttpKernel\Event\ControllerEvent;

final readonly class AuthRequestListener
{
    public function __construct(
        private TokenManagerInterface $tokenManager
    ) {
    }

    public function __invoke(ControllerEvent $event): void
    {
        $request = $event->getRequest();

        $requiresAuth = !empty($event->getAttributes(RequireAuth::class));

        if (!$requiresAuth) {
            return;
        }

        $authHeader = $request->headers->get('Authorization');
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            throw new UnauthorizedException('Missing or malformed Authorization header');
        }

        $token = substr($authHeader, 7);

        try {
            $payload = $this->tokenManager->verify($token);
        } catch (\Throwable $e) {
            throw new UnauthorizedException('Invalid or expired token', previous: $e);
        }

        if (!isset($payload['sub'])) {
            throw new UnauthorizedException('Token does not contain subject (sub)');
        }

        $request->attributes->set('userId', $payload['sub']);
    }
}
