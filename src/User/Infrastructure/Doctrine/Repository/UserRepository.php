<?php

declare(strict_types=1);

namespace App\User\Infrastructure\Doctrine\Repository;

use App\Common\Domain\ValueObject\UserId;
use App\Common\Infrastructure\Doctrine\DoctrineBaseRepository;
use App\Common\Infrastructure\Doctrine\Type\UserIdType;
use App\User\Domain\Entity\User;
use App\User\Domain\Exception\UserNotFoundException;
use App\User\Domain\Repository\UserRepositoryInterface;
use App\User\Domain\ValueObject\Email;
use App\User\Infrastructure\Doctrine\Type\EmailType;
use Doctrine\ORM\QueryBuilder;

final class UserRepository extends DoctrineBaseRepository implements UserRepositoryInterface
{
    protected static function getMappedEntity(): string
    {
        return User::class;
    }

    public function save(User $user, bool $flush = true): void
    {
        $this->entityManager->persist($user);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function delete(User $user, bool $flush = true): void
    {
        $user->delete();
        $this->entityManager->persist($user);
        if ($flush) {
            $this->entityManager->flush();
        }
    }

    public function findById(UserId $id): ?User
    {
        $qb = $this->createActiveQueryBuilder()
                   ->andWhere('u.id = :id')
                   ->setParameter('id', $id, UserIdType::NAME);

        $user = $qb->getQuery()->getOneOrNullResult();

        if (null === $user) {
            throw new UserNotFoundException($id->value());
        }

        return $user;
    }

    public function findByEmail(Email $email): ?User
    {
        $user = $this->createActiveQueryBuilder()
                    ->andWhere('u.email = :email')
                    ->setParameter('email', $email, EmailType::NAME)
                    ->getQuery()
                    ->getOneOrNullResult();

        if (null === $user) {
            throw new UserNotFoundException($email->value());
        }

        return $user;
    }

    public function findPaginated(int $offset, int $limit): iterable
    {
        return $this->createActiveQueryBuilder()
                    ->orderBy('u.createdAt', 'DESC')
                    ->setFirstResult($offset)
                    ->setMaxResults($limit)
                    ->getQuery()
                    ->getResult();
    }

    private function createActiveQueryBuilder(): QueryBuilder
    {
        return $this->entityManager
            ->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.deletedAt IS NULL');
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
