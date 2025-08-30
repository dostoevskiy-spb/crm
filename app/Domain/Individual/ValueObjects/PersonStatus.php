<?php

declare(strict_types=1);

namespace App\Domain\Individual\ValueObjects;

final class PersonStatus
{
    private int $value;

    public function __construct(int $value)
    {
        // Domain-specific constraints can be applied here later
        $this->value = $value;
    }

    public static function fromInt(int $value): self
    {
        return new self($value);
    }

    public function value(): int
    {
        return $this->value;
    }
}
