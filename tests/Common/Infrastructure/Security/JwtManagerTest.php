<?php

declare(strict_types=1);

namespace App\Tests\Common\Infrastructure\Security;

use App\Common\Infrastructure\Security\JwtManager;
use Faker\Factory;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\JWT;
use PHPUnit\Framework\TestCase;

class JwtManagerTest extends TestCase
{
    public function testItGeneratesAndVerifiesToken(): void
    {
        $faker = Factory::create();

        $secret = 'my_secret_key';
        $ttl = 3600;

        $manager = new JwtManager($secret, $ttl);
        $userId = $faker->uuid;

        $token = $manager->generateToken($userId);
        $payload = $manager->verify($token);

        $this->assertArrayHasKey('sub', $payload);
        $this->assertSame($userId, $payload['sub']);
        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
    }

    public function testItFailsVerificationWithInvalidToken(): void
    {
        $this->expectException(\Firebase\JWT\SignatureInvalidException::class);

        $manager = new JwtManager('correct_secret');

        // Token signed with different key
        $badToken = JWT::encode(['sub' => 'test'], 'wrong_secret', 'HS256');

        $manager->verify($badToken);
    }

    public function testItFailsVerificationWithExpiredToken(): void
    {
        $this->expectException(ExpiredException::class);

        $manager = new JwtManager('expired_secret', 1); // 1s TTL
        $token = $manager->generateToken('expired-user');

        sleep(2); // Let it expire

        $manager->verify($token);
    }
}
