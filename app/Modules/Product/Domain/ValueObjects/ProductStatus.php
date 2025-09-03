<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\ValueObjects;

final class ProductStatus
{
    public const ACTIVE = 'active';

    public const INACTIVE = 'inactive';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (! in_array($value, [self::ACTIVE, self::INACTIVE], true)) {
            throw new \InvalidArgumentException('Invalid status value');
        }
        $this->value = $value;
    }

    public static function active(): self
    {
        return new self(self::ACTIVE);
    }

    public static function inactive(): self
    {
        return new self(self::INACTIVE);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isActive(): bool
    {
        return $this->value === self::ACTIVE;
    }
}
