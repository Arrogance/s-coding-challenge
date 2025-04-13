<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Repository;

use App\Common\Domain\ValueObject\UserId;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;

interface WorkEntryRepositoryInterface
{
    public function save(WorkEntry $workEntry): void;

    public function delete(WorkEntry $workEntry): void;

    public function findById(UserId $userId, WorkEntryId $id): ?WorkEntry;

    public function findByUser(UserId $userId): ?WorkEntry;

    /**
     * @return iterable<WorkEntry>
     */
    public function findPaginated(UserId $userId, int $offset, int $limit): iterable;
}
