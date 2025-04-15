<?php

declare(strict_types=1);

namespace App\Tests\User\Application\Service;

use App\User\Application\Service\PasswordHasherService;
use App\User\Domain\ValueObject\Password;
use Faker\Factory;
use PHPUnit\Framework\TestCase;

class PasswordHasherServiceTest extends TestCase
{
    public function testItHashesAndVerifiesPasswordCorrectly(): void
    {
        $faker = Factory::create();
        $plain = $faker->password();

        $service = new PasswordHasherService();

        $hashedPassword = $service->hash($plain);

        $this->assertInstanceOf(Password::class, $hashedPassword);
        $this->assertTrue($service->verify($plain, $hashedPassword));
    }

    public function testItFailsVerificationWithWrongPassword(): void
    {
        $faker = Factory::create();
        $service = new PasswordHasherService();

        $correctPassword = $faker->password();
        $wrongPassword = $faker->password();

        $hashedPassword = $service->hash($correctPassword);

        $this->assertFalse($service->verify($wrongPassword, $hashedPassword));
    }
}
