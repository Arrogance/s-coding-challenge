<?php

declare(strict_types=1);

namespace App\WorkEntry\Application\Command;

use App\Common\Application\Command\Command;

final class UpdateWorkEntryCommand extends Command
{
    public function __construct(
        public readonly string $id,
        public readonly string $userId,
        public readonly \DateTimeImmutable $startDate,
        public readonly \DateTimeImmutable $endDate
    ) {
    }
}
