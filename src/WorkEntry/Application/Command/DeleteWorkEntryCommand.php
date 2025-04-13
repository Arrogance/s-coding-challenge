<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Command;

use App\Common\Application\Command\Command;

class DeleteWorkEntryCommand extends Command
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
    ) {
    }
}
