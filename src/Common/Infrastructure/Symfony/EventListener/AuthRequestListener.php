<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Symfony\EventListener;

use App\Common\Application\Security\TokenManagerInterface;
use App\Common\Infrastructure\Attribute\RequireAuth;
use App\Common\Infrastructure\Exception\UnauthorizedException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\RequestEvent;

final class AuthRequestListener
{
    public function __construct(
        private readonly TokenManagerInterface $tokenManager
    ) {
    }

    public function __invoke(RequestEvent $event): void
    {
        $request = $event->getRequest();

        $controller = $request->attributes->get('_controller');
        if (!\is_string($controller) || !str_contains($controller, '::')) {
            return;
        }

        [$controllerClass, $method] = explode('::', $controller);

        $reflectionClass = new \ReflectionClass($controllerClass);
        $reflectionMethod = $reflectionClass->getMethod($method);

        $requiresAuth = !empty($reflectionClass->getAttributes(RequireAuth::class))
            || !empty($reflectionMethod->getAttributes(RequireAuth::class));

        if (!$requiresAuth) {
            return;
        }

        // Buscar el token
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

        // Guardar el userId en atributos de la request
        if (!isset($payload['sub'])) {
            throw new UnauthorizedException('Token does not contain subject (sub)');
        }

        $request->attributes->set('userId', $payload['sub']);
    }
}
