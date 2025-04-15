<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\Common\Application\Command\Command;

final class LoginCommand extends Command
{
    public function __construct(
        public readonly string $email,
        public readonly string $password
    ) {
    }
}
