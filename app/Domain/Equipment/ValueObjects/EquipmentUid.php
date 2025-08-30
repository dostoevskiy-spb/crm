<?php

declare(strict_types=1);

namespace App\Domain\Equipment\ValueObjects;

final class EquipmentUid
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || !preg_match('/^[0-9a-fA-F\-]{36}$/', $value)) {
            throw new \InvalidArgumentException('Invalid equipment UID');
        }
        $this->value = strtolower($value);
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
