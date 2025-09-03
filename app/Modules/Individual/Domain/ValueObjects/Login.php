<?php

declare(strict_types=1);

namespace App\Modules\Individual\Domain\ValueObjects;

final class Login
{
    private string $value;

    public function __construct(?string $value)
    {
        if ($value === null) {
            $this->value = '';

            return;
        }

        $value = trim($value);
        if ($value !== '' && mb_strlen($value) < 6) {
            throw new \InvalidArgumentException('Login must be at least 6 characters long');
        }
        $this->value = $value;
    }

    public static function fromNullable(?string $value): self
    {
        return new self($value);
    }

    public function isEmpty(): bool
    {
        return $this->value === '';
    }

    public function value(): ?string
    {
        return $this->value === '' ? null : $this->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
