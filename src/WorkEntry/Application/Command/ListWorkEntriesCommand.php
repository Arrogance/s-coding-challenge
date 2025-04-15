<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Command;

use App\Common\Application\Command\Command;

final class ListWorkEntriesCommand extends Command
{
    public function __construct(
        public readonly string $userId,
        public readonly int $offset,
        public readonly int $limit,
    ) {
    }
}
