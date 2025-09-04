<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\Enum;

enum StatusEnum: string
{
    case ACTIVE = 'active';
    case ARCHIVED = 'archived';

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
