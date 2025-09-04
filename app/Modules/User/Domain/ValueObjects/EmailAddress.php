<?php

declare(strict_types=1);

namespace App\Modules\User\Domain\ValueObjects;

final class EmailAddress
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || ! preg_match('/^[A-Za-z0-9._%+\-]+@[A-Za-z0-9\-]+\.[A-Za-z0-9.\-]+$/', $value)) {
            throw new \InvalidArgumentException('Invalid email address');
        }
        $this->value = $value;
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
