<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\Common\Application\Command\Command;

final class PatchUserCommand extends Command
{
    public function __construct(
        public readonly string $id,
        public readonly ?string $name = null,
        public readonly ?string $email = null
    ) {
    }
}
