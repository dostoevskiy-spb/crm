<?php

declare(strict_types=1);

namespace App\Domain\Individual\ValueObjects;

final class PersonUid
{
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if (!self::isValidUuid($value)) {
            throw new \InvalidArgumentException('Invalid UUID value for PersonUid');
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

    private static function isValidUuid(string $uuid): bool
    {
        // Basic RFC 4122 variant check (accept with/without dashes)
        return (bool) preg_match(
            '/^[0-9a-fA-F]{8}-?[0-9a-fA-F]{4}-?[1-5][0-9a-fA-F]{3}-?[89abAB][0-9a-fA-F]{3}-?[0-9a-fA-F]{12}$/',
            $uuid
        );
    }
}
