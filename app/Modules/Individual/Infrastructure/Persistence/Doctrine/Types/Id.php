<?php

declare(strict_types=1);

namespace App\Modules\Individual\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\GuidType;
use Ramsey\Uuid\Uuid;
use Ramsey\Uuid\UuidInterface;

final class Id extends GuidType
{
    public const string NAME = 'individual_id';

    public function getName(): string
    {
        return self::NAME;
    }

    /**
     * @param  Uuid  $value
     */
    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        return $value->toString();
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?UuidInterface
    {
        if ($value === null) {
            return null;
        }

        try {
            return Uuid::fromString($value);
        } catch (\Throwable) {
            throw new \InvalidArgumentException('Invalid individual UUID value: '.$value);
        }
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true; // keep type during schema diff
    }
}
