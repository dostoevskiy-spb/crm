<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\ValueObjects;

final class ProductPrice
{
    private string $value; // normalized decimal string with 2 scale

    public function __construct(string|int|float $value)
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }
        if (is_float($value)) {
            $value = (string) $value;
        }
        $normalized = number_format((float) $value, 2, '.', '');
        if ((float) $normalized < 0) {
            throw new \InvalidArgumentException('Price must be non-negative');
        }
        $this->value = $normalized;
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
