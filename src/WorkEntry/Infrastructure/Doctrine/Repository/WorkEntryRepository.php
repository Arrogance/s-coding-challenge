<?php

declare(strict_types=1);

namespace App\WorkEntry\Infrastructure\Doctrine\Repository;

use App\Common\Domain\ValueObject\UserId;
use App\Common\Infrastructure\Doctrine\DoctrineBaseRepository;
use App\Common\Infrastructure\Doctrine\Type\UserIdType;
use App\WorkEntry\Domain\Entity\WorkEntry;
use App\WorkEntry\Domain\Repository\WorkEntryRepositoryInterface;
use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use App\WorkEntry\Infrastructure\Doctrine\Type\WorkEntryIdType;
use Doctrine\ORM\QueryBuilder;

final class WorkEntryRepository extends DoctrineBaseRepository implements WorkEntryRepositoryInterface
{
    protected static function getMappedEntity(): string
    {
        return WorkEntry::class;
    }

    public function save(WorkEntry $workEntry, bool $flush = true): void
    {
        $this->entityManager->persist($workEntry);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function delete(WorkEntry $workEntry, bool $flush = true): void
    {
        $workEntry->delete();
        $this->entityManager->persist($workEntry);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findById(UserId $userId, WorkEntryId $id): ?WorkEntry
    {
        return $this->createActiveQueryBuilder($userId)
                    ->andWhere('w.id = :id')
                    ->setParameter('id', $id, WorkEntryIdType::NAME)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findByUser(UserId $userId): ?WorkEntry
    {
        return $this->createActiveQueryBuilder($userId)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findPaginated(UserId $userId, int $offset, int $limit): iterable
    {
        return $this->createActiveQueryBuilder($userId)
                    ->orderBy('w.startDate', 'DESC')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    public function iterateActiveByUser(UserId $userId, int $batchSize = 100): \Generator
    {
        $lastId = null;

        do {
            $qb = $this->createActiveQueryBuilder($userId)
                ->orderBy('w.id', 'ASC')
                ->setMaxResults($batchSize);

            if (null !== $lastId) {
                $qb->andWhere('w.id > :lastId')
                   ->setParameter('lastId', $lastId, WorkEntryIdType::NAME);
            }

            $entries = $qb->getQuery()->getResult();

            yield from $entries;

            if ([] !== $entries) {
                $lastId = end($entries)->id();
            }
        } while (\count($entries) === $batchSize);
    }

    private function createActiveQueryBuilder(UserId $userId): QueryBuilder
    {
        return $this->entityManager
            ->getRepository(WorkEntry::class)
            ->createQueryBuilder('w')
            ->where('w.deletedAt IS NULL')
            ->andWhere('w.userId = :userId')
            ->setParameter('userId', $userId, UserIdType::NAME);
    }

    public function beginTransaction(): void
    {
        $this->entityManager->beginTransaction();
    }

    public function commit(): void
    {
        $this->entityManager->commit();
    }

    public function rollback(): void
    {
        $this->entityManager->rollBack();
    }
}
