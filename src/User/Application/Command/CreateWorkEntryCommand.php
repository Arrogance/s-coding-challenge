<?php

declare(strict_types=1);

namespace App\User\Application\Command;

use App\Common\Application\Command\Command;

class CreateWorkEntryCommand extends Command
{
    public function __construct(
        public readonly string $id,
        public readonly \DateTimeImmutable $startDate,
        public readonly \DateTimeImmutable $endDate
    ) {
    }
}
