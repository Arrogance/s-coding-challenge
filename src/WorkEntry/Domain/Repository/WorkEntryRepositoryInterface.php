<?php

declare(strict_types=1);

namespace App\WorkEntry\Domain\Repository;

use App\Common\Domain\ValueObject\UserId;
use App\Common\Domain\ValueObject\WorkEntryId;
use App\WorkEntry\Domain\Entity\WorkEntry;

interface WorkEntryRepositoryInterface
{
    public function save(WorkEntry $workEntry): void;

    public function delete(WorkEntry $workEntry): void;

    public function findById(WorkEntryId $id): ?WorkEntry;

    public function findByUser(UserId $userId): ?WorkEntry;

    /**
     * @return iterable<WorkEntry>
     */
    public function findPaginated(int $offset, int $limit): iterable;
}
