<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\ValueObjects;

final class ProductType
{
    public const ITEM = 'item';

    public const SERVICE = 'service';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (! in_array($value, [self::ITEM, self::SERVICE], true)) {
            throw new \InvalidArgumentException('Invalid product type');
        }
        $this->value = $value;
    }

    public static function item(): self
    {
        return new self(self::ITEM);
    }

    public static function service(): self
    {
        return new self(self::SERVICE);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function isItem(): bool
    {
        return $this->value === self::ITEM;
    }

    public function isService(): bool
    {
        return $this->value === self::SERVICE;
    }
}
