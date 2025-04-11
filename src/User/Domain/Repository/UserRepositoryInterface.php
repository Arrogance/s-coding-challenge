<?php

declare(strict_types=1);

namespace App\User\Domain\Repository;

use App\Common\Domain\ValueObject\UserId;
use App\User\Domain\Entity\User;
use App\User\Domain\ValueObject\Email;

interface UserRepositoryInterface
{
    public function save(User $user): void;

    public function delete(User $user): void;

    public function findById(UserId $id): ?User;

    public function findByEmail(Email $email): ?User;

    /**
     * @return iterable<User>
     */
    public function findPaginated(int $offset, int $limit): iterable;
}
