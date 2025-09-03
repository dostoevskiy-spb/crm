<?php

declare(strict_types=1);

namespace App\Modules\Product\Domain\ValueObjects;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class UnitOfMeasure
{
    #[ORM\Column(name: 'unit', type: 'string', length: 20)]
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > 20) {
            throw new \InvalidArgumentException('Unit of measure must be between 1 and 20 characters');
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
