<?php

declare(strict_types=1);

namespace App\WorkEntry\Infrastructure\Doctrine\Type;

use App\WorkEntry\Domain\ValueObject\WorkEntryId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;
use Symfony\Component\Uid\Uuid;

final class WorkEntryIdType extends Type
{
    public const string NAME = 'work_entry_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'BINARY(16)';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?WorkEntryId
    {
        if (null === $value) {
            return null;
        }

        // Symfony UID binary -> string
        $uuid = Uuid::fromBinary($value)->toRfc4122();

        return new WorkEntryId($uuid);
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (null === $value) {
            return null;
        }

        if (method_exists($value, 'value')) {
            $value = $value->value();
        }

        // string UUID -> binary
        return Uuid::fromString($value)->toBinary();
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
