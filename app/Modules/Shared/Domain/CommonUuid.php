<?php

namespace App\Modules\Shared\Domain;

use InvalidArgumentException;
use Symfony\Component\Uid\Uuid;

class CommonUuid
{
    private Uuid $value;

    public function __construct(string $value)
    {
        if (! Uuid::isValid($value)) {
            throw new InvalidArgumentException('Invalid UUID');
        }
        $this->value = Uuid::fromString($value);
    }

    public static function next(): static
    {
        return new static(Uuid::v4()->toString());
    }

    public function value(): string
    {
        return $this->value->toString();
    }

    public function __toString(): string
    {
        return $this->value->toString();
    }
}
