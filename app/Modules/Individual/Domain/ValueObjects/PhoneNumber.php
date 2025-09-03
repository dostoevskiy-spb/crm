<?php

declare(strict_types=1);

namespace App\Modules\Individual\Domain\ValueObjects;

final class PhoneNumber
{
    private string $e164;

    public function __construct(string $e164)
    {
        $e164 = trim($e164);
        if (! preg_match('/^\+[0-9]{10,15}$/', $e164)) { // простая проверка E.164
            throw new \InvalidArgumentException('Phone must be in E.164 format, e.g. +79991234567');
        }
        $this->e164 = $e164;
    }

    public function value(): string
    {
        return $this->e164;
    }

    public function __toString(): string
    {
        return $this->e164;
    }
}
