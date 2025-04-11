<?php

declare(strict_types=1);

namespace App\User\Application\Service;

use App\User\Domain\ValueObject\Password;

class PasswordHasherService
{
    public function hash(string $plain): Password
    {
        $hash = password_hash($plain, \PASSWORD_BCRYPT);

        return new Password($hash);
    }

    public function verify(string $plain, Password $hash): bool
    {
        return password_verify($plain, $hash->value());
    }
}
