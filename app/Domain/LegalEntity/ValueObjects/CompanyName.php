<?php

declare(strict_types=1);

namespace App\Domain\LegalEntity\ValueObjects;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Embeddable]
final class CompanyName
{
    #[ORM\Column(name: 'short_name', type: 'string', length: 20)]
    private string $shortName;
    #[ORM\Column(name: 'full_name', type: 'string', length: 255)]
    private string $fullName;

    public function __construct(string $shortName, string $fullName)
    {
        $shortName = trim($shortName);
        $fullName = trim($fullName);

        if ($shortName === '' || mb_strlen($shortName) > 20) {
            throw new \InvalidArgumentException('Short name must be between 1 and 20 characters');
        }

        if ($fullName === '' || mb_strlen($fullName) > 255) {
            throw new \InvalidArgumentException('Full name must be between 1 and 255 characters');
        }

        $this->shortName = $shortName;
        $this->fullName = $fullName;
    }

    public function shortName(): string
    {
        return $this->shortName;
    }

    public function fullName(): string
    {
        return $this->fullName;
    }

    public function __toString(): string
    {
        return $this->shortName;
    }
}
