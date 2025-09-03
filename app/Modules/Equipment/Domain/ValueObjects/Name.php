<?php

declare(strict_types=1);

namespace App\Modules\Equipment\Domain\ValueObjects;

use Doctrine\ORM\Mapping as ORM;

final class Name
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > 100) {
            throw new \InvalidArgumentException('Equipment name must be between 1 and 100 characters');
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
