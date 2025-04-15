<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\Common\Application\Command\Command;

final class ResetUserPasswordCommand extends Command
{
    public function __construct(
        public readonly string $id,
        public readonly string $newPassword
    ) {
    }
}
