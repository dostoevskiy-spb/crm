<?php

namespace App\Modules\Product\Domain\Enum;

enum StatusEnum: string
{
    case ACTIVE = 'active';
    case INACTIVE = 'inactive';

    public function active(): self
    {
        return self::ACTIVE;
    }

    public static function inactive(): self
    {
        return self::INACTIVE;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this === self::ACTIVE;
    }
}
