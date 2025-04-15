<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Repository;

use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;

interface WorkEntryRepositoryInterface
{
    public function save(WorkEntry $workEntry, bool $flush = true): void;

    public function delete(WorkEntry $workEntry, bool $flush = true): void;

    public function findById(UserId $userId, WorkEntryId $id): ?WorkEntry;

    public function findByUser(UserId $userId): ?WorkEntry;

    /**
     * @return iterable<WorkEntry>
     */
    public function findPaginated(UserId $userId, int $offset, int $limit): iterable;

    public function iterateActiveByUser(UserId $userId, int $batchSize = 100): \Generator;

    public function beginTransaction(): void;

    public function commit(): void;

    public function rollback(): void;
}
