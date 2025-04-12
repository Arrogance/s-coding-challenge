<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Security;

use App\Common\Application\Security\TokenManagerInterface;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

readonly class JwtManager implements TokenManagerInterface
{
    public function __construct(
        private string $secret,
        private int $ttlInSeconds = 3600
    ) {
    }

    public function generateToken(string $id): string
    {
        $now = time();
        $payload = [
            'sub' => $id,
            'iat' => $now,
            'exp' => $now + $this->ttlInSeconds,
        ];

        return JWT::encode($payload, $this->secret, 'HS256');
    }

    public function verify(string $token): array
    {
        return (array) JWT::decode($token, new Key($this->secret, 'HS256'));
    }
}
