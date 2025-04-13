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

    public function save(WorkEntry $workEntry): void
    {
        $this->entityManager->persist($workEntry);
        $this->entityManager->flush();
    }

    public function delete(WorkEntry $workEntry): void
    {
        //        $workEntry->delete();
        $this->entityManager->persist($workEntry);
        $this->entityManager->flush();
    }

    public function findById(UserId $userId, WorkEntryId $id): ?WorkEntry
    {
        return $this->createActiveQueryBuilder()
                    ->andWhere('w.id = :id')
                    ->andWhere('w.userId = :userId')
                    ->setParameter('id', $id, WorkEntryIdType::NAME)
                    ->setParameter('userId', $userId, UserIdType::NAME)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findByUser(UserId $userId): ?WorkEntry
    {
        return $this->createActiveQueryBuilder()
                    ->andWhere('w.userId = :userId')
                    ->setParameter('userId', $userId, UserIdType::NAME)
                    ->getQuery()
                    ->getOneOrNullResult();
    }

    public function findPaginated(UserId $userId, int $offset, int $limit): iterable
    {
        return $this->createActiveQueryBuilder()
                    ->andWhere('w.userId = :userId')
                    ->setParameter('userId', $userId, UserIdType::NAME)
                    ->orderBy('w.startDate', 'DESC')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    private function createActiveQueryBuilder(): QueryBuilder
    {
        return $this->entityManager
            ->getRepository(WorkEntry::class)
            ->createQueryBuilder('w')
            ->where('w.deletedAt IS NULL');
    }
}
