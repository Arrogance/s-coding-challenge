<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Doctrine;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;

abstract class DoctrineBaseRepository extends EntityRepository
{
    public function __construct(protected readonly EntityManagerInterface $entityManager)
    {
        parent::__construct($entityManager, $entityManager->getClassMetadata(static::getMappedEntity()));
    }

    abstract protected static function getMappedEntity(): string;
}
