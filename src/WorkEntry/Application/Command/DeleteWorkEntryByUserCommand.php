<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Command;

use App\Common\Application\Command\Command;

class DeleteWorkEntryByUserCommand extends Command
{
    public function __construct(public readonly string $userId)
    {
    }
}
