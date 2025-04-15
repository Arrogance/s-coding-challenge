<?php

declare(strict_types=1);

namespace App\Tests\Doctrine\Type;

use App\Common\Domain\ValueObject\UserId;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class UserIdType extends Type
{
    public const string NAME = 'user_id';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'TEXT';
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UserId
    {
        if (null === $value) {
            return null;
        }

        return new UserId((string) $value);
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
