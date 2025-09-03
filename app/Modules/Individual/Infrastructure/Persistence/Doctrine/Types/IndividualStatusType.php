<?php

declare(strict_types=1);

namespace App\Modules\Individual\Infrastructure\Persistence\Doctrine\Types;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

/**
 * Maps DB string values ('active'|'archived') to PHP int codes (1|2) and back.
 */
final class IndividualStatusType extends StringType
{
    public const NAME = 'individual_status';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if ($value === null) {
            return null;
        }

        // PHP value expected: int (1|2)
        return match ((int) $value) {
            1 => 'active',
            2 => 'archived',
            default => throw new \InvalidArgumentException('Invalid individual status int value: '.$value),
        };
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?int
    {
        if ($value === null) {
            return null;
        }

        // DB value expected: string ('active'|'archived')
        return match ((string) $value) {
            'active' => 1,
            'archived' => 2,
            default => throw new \InvalidArgumentException('Invalid individual status DB value: '.$value),
        };
    }

    public function requiresSQLCommentHint(AbstractPlatform $platform): bool
    {
        return true; // keep type during schema diff
    }
}
