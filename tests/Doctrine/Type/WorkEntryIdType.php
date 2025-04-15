<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Type;

use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class WorkEntryIdType extends Type
{
    public const string NAME = 'work_entry_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'TEXT';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?WorkEntryId
    {
        if (null === $value) {
            return null;
        }

        return new WorkEntryId((string) $value);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        return method_exists($value, 'value')
            ? (string) $value->value()
            : (string) $value;
    }

    public function getName(): string
    {
        return self::NAME;
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true;
    }
}
