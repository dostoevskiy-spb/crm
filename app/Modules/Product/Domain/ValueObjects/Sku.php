<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\ValueObjects;

final class Sku
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > 50) {
            throw new \InvalidArgumentException('SKU must be between 1 and 50 characters');
        }
        $this->value = $value;
    }

    public static function fromString(string $value): self
    {
        return new self($value);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
