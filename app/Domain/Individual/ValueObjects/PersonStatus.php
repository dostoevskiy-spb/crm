<?php

declare(strict_types=1);

namespace App\Domain\Individual\ValueObjects;

final class PersonStatus
{
    private int $value;

    public function __construct(int $value)
    {
        // Allowed statuses: 1 - active, 2 - archived (can be extended later)
        if (! in_array($value, [1, 2], true)) {
            throw new \InvalidArgumentException('Invalid person status');
        }
        $this->value = $value;
    }

    public function value(): int
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return (string) $this->value;
    }
}
