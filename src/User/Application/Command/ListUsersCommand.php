<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\Common\Application\Command\Command;

final class ListUsersCommand extends Command
{
    public function __construct(
        public readonly int $offset,
        public readonly int $limit,
    ) {
    }
}
