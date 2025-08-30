<?php

declare(strict_types=1);

namespace App\Domain\Product\ValueObjects;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class ProductName
{
    #[ORM\Column(name: 'name', type: 'string', length: 50)]
    private string $value;

    public function __construct(string $value)
    {
        $value = trim($value);
        if ($value === '' || mb_strlen($value) > 50) {
            throw new \InvalidArgumentException('Product name must be between 1 and 50 characters');
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
