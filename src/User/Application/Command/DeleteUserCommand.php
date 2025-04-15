<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\Common\Application\Command\Command;

final class DeleteUserCommand extends Command
{
    public function __construct(
        public readonly string $id,
    ) {
    }
}
